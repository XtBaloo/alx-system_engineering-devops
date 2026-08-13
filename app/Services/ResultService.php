<?php

namespace App\Services;

use App\Models\AssessmentScore;
use App\Models\ExaminationScore;
use App\Models\GradingScale;
use App\Models\Result;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\DB;

class ResultService
{
    /**
     * Recalculate (or create) the Result row for a single student/subject/term
     * from its underlying assessment + examination scores.
     */
    public function recalculateResult(int $studentId, int $subjectId, int $classArmId, int $academicSessionId, int $termId): Result
    {
        $scoreFilter = ['student_id' => $studentId, 'subject_id' => $subjectId, 'term_id' => $termId];

        $result = Result::firstOrNew([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId,
        ]);

        // Never silently overwrite an already-published result's figures.
        if ($result->exists && $result->status === 'published') {
            return $result;
        }

        $hasAssessmentScores = AssessmentScore::where($scoreFilter)->exists();
        $hasExaminationScore = ExaminationScore::where($scoreFilter)->exists();

        // Nothing recorded yet for this student/subject/term: don't create a
        // placeholder result with a misleading grade before any score exists.
        if (! $result->exists && ! $hasAssessmentScores && ! $hasExaminationScore) {
            return $result;
        }

        $assessmentTotal = AssessmentScore::where($scoreFilter)->sum('score');
        $examinationScore = ExaminationScore::where($scoreFilter)->value('score') ?? 0;

        $total = round((float) $assessmentTotal + (float) $examinationScore, 2);
        $gradeRow = GradingScale::forScore($total);

        $result->class_arm_id = $classArmId;
        $result->academic_session_id = $academicSessionId;
        $result->assessment_total = $assessmentTotal;
        $result->examination_score = $examinationScore;
        $result->total_score = $total;
        $result->grade = $gradeRow->grade ?? null;
        $result->remark = $gradeRow->remark ?? null;
        $result->grade_point = $gradeRow->grade_point ?? null;
        $result->status = $result->exists && in_array($result->status, ['submitted', 'reviewed', 'approved']) ? 'draft' : ($result->status ?: 'draft');
        $result->save();

        return $result;
    }

    /**
     * Recompute class positions for every student offering a given subject,
     * within a class arm and term. Supports standard-competition or dense ranking.
     */
    public function recalculatePositions(int $classArmId, int $subjectId, int $termId): void
    {
        $rankingMethod = SchoolSetting::current()->ranking_method;

        $results = Result::where([
            'class_arm_id' => $classArmId,
            'subject_id' => $subjectId,
            'term_id' => $termId,
        ])->orderByDesc('total_score')->get();

        $classSize = $results->count();

        DB::transaction(function () use ($results, $classSize, $rankingMethod) {
            $position = 0;
            $rank = 0;
            $previousScore = null;

            foreach ($results as $index => $result) {
                if ($previousScore === null || (float) $result->total_score !== $previousScore) {
                    $rank = $rankingMethod === 'dense' ? $rank + 1 : $index + 1;
                }

                $result->position = $rank;
                $result->subject_class_size = $classSize;
                $result->saveQuietly();

                $previousScore = (float) $result->total_score;
            }
        });
    }
}
