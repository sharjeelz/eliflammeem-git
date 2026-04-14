<?php

namespace App\Services;

use Illuminate\Support\Str;

class MetadataExtractionService
{
    /**
     * Extract structured metadata from chunk content.
     *
     * @param string $content Chunk text content
     * @return array Extracted metadata
     */
    public function extractMetadata(string $content): array
    {
        $metadata = [];

        // Extract grade ranges (e.g., "KG to Grade 10", "Grade 11 and 12", "Class 3")
        $metadata['grade_ranges'] = $this->extractGradeRanges($content);

        // Extract specific grades/classes mentioned
        $metadata['grades_mentioned'] = $this->extractGradesMentioned($content);

        // Extract fee amounts (e.g., "SR 415", "SAR 100")
        $metadata['fee_amounts'] = $this->extractFeeAmounts($content);

        // Extract dates (e.g., "20th of the month", "April", "5th of October")
        $metadata['dates'] = $this->extractDates($content);

        // Extract age ranges (e.g., "8 years old", "ages 4-5")
        $metadata['age_ranges'] = $this->extractAgeRanges($content);

        // Detect topic/category based on keywords
        $metadata['topics'] = $this->detectTopics($content);

        return $metadata;
    }

    /**
     * Extract grade ranges from text.
     * Examples: "KG to Grade 10", "Grade 11 and 12", "Play Group to Nursery"
     *
     * @param string $content
     * @return array [['start' => 'KG', 'end' => 'Grade 10', 'includes' => [0, 1, 2, ..., 10]]]
     */
    protected function extractGradeRanges(string $content): array
    {
        $ranges = [];

        // Pattern 1: "KG to Grade 10" or "Grade 1 to Grade 5"
        preg_match_all(
            '/(?:KG|Grade|Class|Nursery|Play\s*Group)\s*(\d*)\s*(?:to|-|–)\s*(?:Grade|Class)\s*(\d+)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $start = $this->normalizeGrade($match[0]);
            $end = isset($match[2]) ? (int)$match[2] : null;
            
            if ($end !== null) {
                $ranges[] = [
                    'start' => $start['grade'],
                    'end' => $end,
                    'includes' => range($start['grade_num'], $end),
                    'raw' => $match[0],
                ];
            }
        }

        // Pattern 2: "Grade 11 and 12" or "Grades 9, 10, 11"
        preg_match_all(
            '/(?:Grade|Class)s?\s*(\d+)(?:\s*(?:and|,)\s*(\d+))+/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            preg_match_all('/\d+/', $match[0], $grades);
            $gradeNumbers = array_map('intval', $grades[0]);
            
            if (!empty($gradeNumbers)) {
                $ranges[] = [
                    'start' => min($gradeNumbers),
                    'end' => max($gradeNumbers),
                    'includes' => $gradeNumbers,
                    'raw' => $match[0],
                ];
            }
        }

        return $ranges;
    }

    /**
     * Extract individual grades mentioned (not ranges).
     *
     * @param string $content
     * @return array [0, 1, 2, 3, ..., 12]
     */
    protected function extractGradesMentioned(string $content): array
    {
        $grades = [];

        // Match "KG", "Nursery", "Play Group"
        if (preg_match('/\b(?:KG|kindergarten)\b/i', $content)) {
            $grades[] = 0;
        }
        if (preg_match('/\b(?:Nursery)\b/i', $content)) {
            $grades[] = -1; // Pre-KG
        }
        if (preg_match('/\b(?:Play\s*Group)\b/i', $content)) {
            $grades[] = -2; // Pre-Nursery
        }

        // Match "Grade X" or "Class X"
        preg_match_all('/\b(?:Grade|Class)\s*(\d+)\b/i', $content, $matches);
        foreach ($matches[1] as $grade) {
            $grades[] = (int)$grade;
        }

        return array_unique($grades);
    }

    /**
     * Extract fee amounts from text.
     * Examples: "SR 415", "SAR 100", "SR. 50"
     *
     * @param string $content
     * @return array [['amount' => 415, 'currency' => 'SR', 'raw' => 'SR 415']]
     */
    protected function extractFeeAmounts(string $content): array
    {
        $amounts = [];

        // Match "SR 415", "SAR 100", "SR. 50", etc.
        preg_match_all(
            '/\b(?:SR|SAR)\.?\s*(\d+(?:[,\/]\d+)?)\b/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $amount = (int)str_replace([',', '/'], '', $match[1]);
            $amounts[] = [
                'amount' => $amount,
                'currency' => 'SR',
                'raw' => $match[0],
            ];
        }

        return $amounts;
    }

    /**
     * Extract dates and deadlines from text.
     *
     * @param string $content
     * @return array ['20th of the month', 'April', '5th of October']
     */
    protected function extractDates(string $content): array
    {
        $dates = [];

        // Match day of month: "20th of the month", "5th of October"
        preg_match_all(
            '/\b(\d{1,2})(?:st|nd|rd|th)\s+(?:of\s+)?(?:the\s+)?(?:month|January|February|March|April|May|June|July|August|September|October|November|December)\b/i',
            $content,
            $matches
        );

        foreach ($matches[0] as $date) {
            $dates[] = $date;
        }

        // Match month names alone
        preg_match_all(
            '/\b(January|February|March|April|May|June|July|August|September|October|November|December)\b/i',
            $content,
            $matches
        );

        foreach ($matches[0] as $month) {
            if (!in_array($month, $dates)) {
                $dates[] = $month;
            }
        }

        return array_unique($dates);
    }

    /**
     * Extract age ranges from text.
     * Examples: "8 years old", "ages 4-5", "4 to 5 years"
     *
     * @param string $content
     * @return array [['min' => 4, 'max' => 5]]
     */
    protected function extractAgeRanges(string $content): array
    {
        $ages = [];

        // Pattern 1: "8 years old"
        preg_match_all('/\b(\d+)\s*years?\s*old\b/i', $content, $matches);
        foreach ($matches[1] as $age) {
            $ages[] = ['min' => (int)$age, 'max' => (int)$age];
        }

        // Pattern 2: "ages 4-5" or "4 to 5 years"
        preg_match_all('/\b(?:ages?\s*)?(\d+)\s*(?:to|-|–)\s*(\d+)\s*years?\b/i', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $ages[] = ['min' => (int)$match[1], 'max' => (int)$match[2]];
        }

        return $ages;
    }

    /**
     * Detect topics/categories based on keywords.
     *
     * @param string $content
     * @return array ['fees', 'admission', 'enrollment', etc.]
     */
    protected function detectTopics(string $content): array
    {
        $topics = [];
        $contentLower = Str::lower($content);

        $topicKeywords = [
            'fees' => ['fee', 'tuition', 'payment', 'cost', 'charge', 'discount'],
            'admission' => ['admission', 'enroll', 'register', 'application'],
            'late_fees' => ['late fee', 'surcharge', 'penalty', 'fine'],
            'payment_schedule' => ['due date', 'deadline', '20th', 'monthly', 'payment schedule'],
            'transport' => ['bus', 'transport', 'transportation'],
            'exam' => ['exam', 'examination', 'test', 'board', 'fbise'],
            'discipline' => ['discipline', 'conduct', 'behavior', 'misconduct'],
            'absence' => ['absence', 'absent', 'leave'],
            're_admission' => ['re-admission', 'readmission', 'struck off'],
            'grades' => ['grade', 'class', 'kg', 'nursery'],
        ];

        foreach ($topicKeywords as $topic => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($contentLower, $keyword)) {
                    $topics[] = $topic;
                    break;
                }
            }
        }

        return array_unique($topics);
    }

    /**
     * Normalize grade names to numeric values.
     *
     * @param string $grade
     * @return array ['grade' => 'KG', 'grade_num' => 0]
     */
    protected function normalizeGrade(string $grade): array
    {
        $gradeLower = Str::lower($grade);

        if (preg_match('/play\s*group/i', $gradeLower)) {
            return ['grade' => 'Play Group', 'grade_num' => -2];
        }
        if (preg_match('/nursery/i', $gradeLower)) {
            return ['grade' => 'Nursery', 'grade_num' => -1];
        }
        if (preg_match('/kg|kindergarten/i', $gradeLower)) {
            return ['grade' => 'KG', 'grade_num' => 0];
        }

        // Extract number from "Grade X" or "Class X"
        if (preg_match('/(\d+)/', $grade, $matches)) {
            $num = (int)$matches[1];
            return ['grade' => $num, 'grade_num' => $num];
        }

        return ['grade' => $grade, 'grade_num' => null];
    }
}
