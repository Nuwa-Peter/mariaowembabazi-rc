<?php // calculation_utils.php

if (!function_exists('getGradeFromScoreUtil')) {
    function getGradeFromScoreUtil($score) {
        if ($score === null || $score === '' || $score === 'N/A' || !is_numeric($score)) return '-';
        $score = floatval($score);
        if ($score > 100 || $score < 0) return '-';
        if ($score >= 90) return 'D1';
        if ($score >= 80) return 'D2';
        if ($score >= 70) return 'C3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'P7';
        if ($score >= 40) return 'P8';
        return 'F9';
    }
}

if (!function_exists('calculateP4P7_BOT_OverallPerformanceUtil')) {
    // Calculates BOT aggregate points and division for P4-P7
    function calculateP4P7_BOT_OverallPerformanceUtil($studentSubjectsDataWithScores, $coreSubjectKeys, $gradingScalePointsMap) {
        $aggregatePoints = 0;
        $coreBOTMissingOrInvalidCount = 0;
        $validCoreBOTScoresExist = false;

        if (empty($gradingScalePointsMap)) { // Fallback, though caller should provide it.
            $gradingScalePointsMap = ['D1'=>1, 'D2'=>2, 'C3'=>3, 'C4'=>4, 'C5'=>5, 'C6'=>6, 'P7'=>7, 'P8'=>8, 'F9'=>9, '-'=>0];
        }

        foreach ($coreSubjectKeys as $coreSubKey) {
            $subjectInfo = $studentSubjectsDataWithScores[$coreSubKey] ?? null;
            // Use bot_score for BOT calculations
            if ($subjectInfo && isset($subjectInfo['bot_score']) && $subjectInfo['bot_score'] !== '-' && is_numeric($subjectInfo['bot_score'])) {
                $validCoreBOTScoresExist = true;
                $botGrade = getGradeFromScoreUtil($subjectInfo['bot_score']);
                $aggregatePoints += getPointsFromGradeUtil($botGrade, $gradingScalePointsMap);
            } else {
                $coreBOTMissingOrInvalidCount++;
            }
        }

        if ($coreBOTMissingOrInvalidCount > 0) {
            return ['p4p7_aggregate_bot_score' => 0, 'p4p7_division_bot' => 'X'];
        }

        $division = 'Ungraded';
        if ($aggregatePoints >= 35 && $aggregatePoints <= 36) $division = 'U';
        else if ($aggregatePoints >= 30 && $aggregatePoints <= 34) $division = 'IV';
        else if ($aggregatePoints >= 25 && $aggregatePoints <= 29) $division = 'III';
        else if ($aggregatePoints >= 13 && $aggregatePoints <= 24) $division = 'II';
        else if ($aggregatePoints >= 4 && $aggregatePoints <= 12) $division = 'I';

        return ['p4p7_aggregate_bot_score' => $aggregatePoints, 'p4p7_division_bot' => $division];
    }
}

if (!function_exists('calculateP4P7_MOT_OverallPerformanceUtil')) {
    // Calculates MOT aggregate points and division for P4-P7
    function calculateP4P7_MOT_OverallPerformanceUtil($studentSubjectsDataWithScores, $coreSubjectKeys, $gradingScalePointsMap) {
        $aggregatePoints = 0;
        $coreMOTMissingOrInvalidCount = 0;
        $validCoreMOTScoresExist = false;

        if (empty($gradingScalePointsMap)) { // Fallback, though caller should provide it.
            $gradingScalePointsMap = ['D1'=>1, 'D2'=>2, 'C3'=>3, 'C4'=>4, 'C5'=>5, 'C6'=>6, 'P7'=>7, 'P8'=>8, 'F9'=>9, '-'=>0];
        }

        foreach ($coreSubjectKeys as $coreSubKey) {
            $subjectInfo = $studentSubjectsDataWithScores[$coreSubKey] ?? null;
            // Use mot_score for MOT calculations
            if ($subjectInfo && isset($subjectInfo['mot_score']) && $subjectInfo['mot_score'] !== '-' && is_numeric($subjectInfo['mot_score'])) {
                $validCoreMOTScoresExist = true;
                $motGrade = getGradeFromScoreUtil($subjectInfo['mot_score']);
                $aggregatePoints += getPointsFromGradeUtil($motGrade, $gradingScalePointsMap);
            } else {
                $coreMOTMissingOrInvalidCount++;
            }
        }

        if ($coreMOTMissingOrInvalidCount > 0) {
            return ['p4p7_aggregate_mot_score' => 0, 'p4p7_division_mot' => 'X'];
        }

        $division = 'Ungraded';
        if ($aggregatePoints >= 35 && $aggregatePoints <= 36) $division = 'U';
        else if ($aggregatePoints >= 30 && $aggregatePoints <= 34) $division = 'IV';
        else if ($aggregatePoints >= 25 && $aggregatePoints <= 29) $division = 'III';
        else if ($aggregatePoints >= 13 && $aggregatePoints <= 24) $division = 'II';
        else if ($aggregatePoints >= 4 && $aggregatePoints <= 12) $division = 'I';

        return ['p4p7_aggregate_mot_score' => $aggregatePoints, 'p4p7_division_mot' => $division];
    }
}

if (!function_exists('getPointsFromGradeUtil')) {
    function getPointsFromGradeUtil($grade, $gradingScalePointsMap) {
        if ($grade === '-' && !isset($gradingScalePointsMap['-'])) return 0;
        return isset($gradingScalePointsMap[$grade]) ? (int)$gradingScalePointsMap[$grade] : 0;
    }
}

if (!function_exists('getSubjectEOTRemarkUtil')) {
    function getSubjectEOTRemarkUtil($eotScore, $remarksScoreMap) {
        if ($eotScore === null || $eotScore === '' || $eotScore === '-' || !is_numeric($eotScore)) return '-';
        $eotScore = floatval($eotScore);
        if ($eotScore < 0 || $eotScore > 100) return '-';
        krsort($remarksScoreMap);
        foreach ($remarksScoreMap as $minScore => $remark) {
            if ($eotScore >= $minScore) {
                return $remark;
            }
        }
        return 'Fail';
    }
}

if (!function_exists('calculateP4P7OverallPerformanceUtil')) {
    // Note: $gradingScalePointsMap MUST be passed by the calling script (e.g., run_calculations.php)
    function calculateP4P7OverallPerformanceUtil($studentCoreSubjectsDataWithScores, $coreSubjectKeys, $gradingScalePointsMap) {
        $aggregatePoints = 0;
        $coreEOTMissingOrInvalidCount = 0;
        $validCoreEOTScoresExist = false;

        if (empty($gradingScalePointsMap)) { // Fallback, though caller should provide it.
            $gradingScalePointsMap = ['D1'=>1, 'D2'=>2, 'C3'=>3, 'C4'=>4, 'C5'=>5, 'C6'=>6, 'P7'=>7, 'P8'=>8, 'F9'=>9, '-'=>0];
        }

        foreach ($coreSubjectKeys as $coreSubKey) {
            $subjectInfo = $studentCoreSubjectsDataWithScores[$coreSubKey] ?? null;
            if ($subjectInfo && isset($subjectInfo['eot_score']) && $subjectInfo['eot_score'] !== '-' && is_numeric($subjectInfo['eot_score'])) {
                $validCoreEOTScoresExist = true;
                $eotGrade = getGradeFromScoreUtil($subjectInfo['eot_score']);
                $aggregatePoints += getPointsFromGradeUtil($eotGrade, $gradingScalePointsMap);
            } else {
                $coreEOTMissingOrInvalidCount++;
            }
        }

        if (!$validCoreEOTScoresExist && $coreEOTMissingOrInvalidCount === count($coreSubjectKeys)) {
            return ['p4p7_aggregate_points' => 0, 'p4p7_division' => 'X']; // MODIFIED
        }

        $division = 'Ungraded';
        if ($aggregatePoints >= 35 && $aggregatePoints <= 36) $division = 'U'; // MODIFIED
        else if ($aggregatePoints >= 30 && $aggregatePoints <= 34) $division = 'IV'; // MODIFIED
        else if ($aggregatePoints >= 25 && $aggregatePoints <= 29) $division = 'III'; // MODIFIED
        else if ($aggregatePoints >= 13 && $aggregatePoints <= 24) $division = 'II'; // MODIFIED
        else if ($aggregatePoints >= 4 && $aggregatePoints <= 12) $division = 'I'; // MODIFIED

        // New rule: Adjust division if English or Maths has grade F9
        $eng_grade = $studentCoreSubjectsDataWithScores['english']['eot_grade'] ?? null;
        $mtc_grade = $studentCoreSubjectsDataWithScores['mtc']['eot_grade'] ?? null;

        // F9 rule now only applies to Division I and II, as per user request.
        if (($eng_grade === 'F9' || $mtc_grade === 'F9') && ($division === 'I' || $division === 'II')) {
            if ($division === 'I') {
                $division = 'II';
            } elseif ($division === 'II') {
                $division = 'III';
            }
        }

        return ['p4p7_aggregate_points' => $aggregatePoints, 'p4p7_division' => $division];
    }
}


if (!function_exists('generateClassTeacherRemarkUtil')) {
    function generateClassTeacherRemarkUtil($performanceData, $isP4_P7) {
        if ($isP4_P7) {
            // Remarks for P4-P7 are based on aggregate points
            $aggregate = $performanceData['p4p7_aggregate_points'] ?? 99; // Default to a high aggregate if not set

            if ($aggregate >= 4 && $aggregate <= 6) {
                return "Excellent work. Keep it up.";
            } elseif ($aggregate >= 7 && $aggregate <= 9) {
                return "Very good work! Keep it up.";
            } elseif ($aggregate >= 10 && $aggregate <= 12) {
                return "Good work. Keep it up.";
            } elseif ($aggregate >= 13 && $aggregate <= 16) {
                return "A good effort. Focus on attaining first grade.";
            } elseif ($aggregate >= 17 && $aggregate <= 24) {
                return "You have the potential to make it if you put in more effort.";
            } elseif ($aggregate >= 25 && $aggregate <= 29) {
                return "There is room for improvement.";
            } elseif ($aggregate >= 30 && $aggregate <= 34) {
                return "You need to put in more effort in your studies.";
            } elseif ($aggregate >= 35) { // Covers 35-36 and above (Division U or higher aggregates)
                return "Please work harder next term for a better performance.";
            } else { // Should not be reached if aggregate is always >= 4 for graded students, but as a fallback.
                $division = $performanceData['p4p7_division'] ?? 'X'; // Use division for X case
                if ($division === 'X') {
                    return "Avoid missing Exams.";
                }
                return "Please see your class teacher to discuss your performance."; // Generic fallback
            }
        } else { // P1-P3
            $position = $performanceData['p1p3_position_in_class'] ?? 0;

            if ($position > 0) {
                if ($position <= 10) {
                    return "Excellent work! Keep it up.";
                } elseif ($position <= 20) {
                    return "Very good work this term. Your position is impressive.";
                } elseif ($position <= 30) {
                    return "Good effort. Push for a better position next term.";
                } elseif ($position <= 40) {
                    return "A fair result. More focus can lead to improvement.";
                } elseif ($position <= 50) {
                    return "You can do better. Let's work together to improve.";
                } elseif ($position <= 70) {
                    return "More effort is needed to improve your position.";
                } elseif ($position <= 80) {
                    return "Your position needs improvement. Hard work is key.";
                } elseif ($position <= 90) {
                    return "You need to work harder to improve your position.";
                } elseif ($position <= 100) {
                    return "Your position is very low. Please work harder.";
                } else {
                    return "Much more effort is required in your studies.";
                }
            }
            // Fallback if position is not available
            return "Your performance this term has been noted. More effort is encouraged for the coming term.";
        }
    }
}

if (!function_exists('generateHeadTeacherRemarkUtil')) {
    function generateHeadTeacherRemarkUtil($performanceData, $isP4_P7) {
        if ($isP4_P7) {
            // Remarks for P4-P7 are based on aggregate points
            $aggregate = $performanceData['p4p7_aggregate_points'] ?? 99; // Default to a high aggregate if not set

            if ($aggregate >= 4 && $aggregate <= 6) {
                return "Excellent work done. Keep it up.";
            } elseif ($aggregate >= 7 && $aggregate <= 9) {
                return "This is a very good result. Keep it up.";
            } elseif ($aggregate >= 10 && $aggregate <= 12) {
                return "Well done on this excellent result. Keep working hard.";
            } elseif ($aggregate >= 13 && $aggregate <= 16) {
                return "A commendable performance. Continue to aim higher and build on this success.";
            } elseif ($aggregate >= 17 && $aggregate <= 24) {
                return "You have the potential to perform better if you concentrate on your studies.";
            } elseif ($aggregate >= 25 && $aggregate <= 29) {
                return "Put in more effort for better performance.";
            } elseif ($aggregate >= 30 && $aggregate <= 34) {
                return "You need to improve in all subjects.";
            } elseif ($aggregate >= 35) { // Covers 35-36 and above (Division U or higher aggregates)
                return "You need to improve in all subjects.";
            } else { // Should not be reached if aggregate is always >= 4 for graded students, but as a fallback.
                $division = $performanceData['p4p7_division'] ?? 'X'; // Use division for X case
                 if ($division === 'X') {
                    return "Exams are important. The school expects you to try your best in all school work.";
                }
                return "The school encourages you to focus on your studies for better results."; // Generic fallback
            }
        } else { // P1-P3
            $position = $performanceData['p1p3_position_in_class'] ?? 0;

            if ($position > 0) {
                if ($position <= 10) {
                    return "Excellent work, Keep it up.";
                } elseif ($position <= 20) {
                    return "A very good performance. Keep aiming higher.";
                } elseif ($position <= 30) {
                    return "A good performance. Strive for a better position next term.";
                } elseif ($position <= 40) {
                    return "A good performance. Keep working hard.";
                } elseif ($position <= 50) {
                    return "A good performance. Keep working harder.";
                } elseif ($position <= 70) {
                    return "There's still room for improvement, dont relax.";
                } elseif ($position <= 80) {
                    return "You need to concentrate on your studies for a better performance.";
                } elseif ($position <= 90) {
                    return "You need to concentrate on your studies for a better performance.";
                } elseif ($position <= 100) {
                    return "You need to concentrate on your studies for a better performance.";
                } else {
                    return "More effort is still needed in all learning areas.";
                }
            }
            // Fallback if position is not available
            return "The school encourages you to focus on your studies for better results.";
        }
    }
}

if (!function_exists('generateNurseryClassTeacherRemarkUtil')) {
    function generateNurseryClassTeacherRemarkUtil($averageScore) {
        if ($averageScore === null || !is_numeric($averageScore)) {
            return "Performance could not be assessed as marks were not available. Please see the class teacher.";
        }

        if ($averageScore >= 90) {
            return "An outstanding performance this term! The pupil has shown great enthusiasm for learning and grasps new concepts quickly. Keep up the wonderful work.";
        } elseif ($averageScore >= 80) {
            return "A very good performance. The pupil participates well in class activities and has a positive attitude towards learning. Continue this great effort.";
        } elseif ($averageScore >= 60) {
            return "A good and steady performance this term. The pupil is making good progress in all learning areas. Consistent effort will lead to even better results.";
        } elseif ($averageScore >= 40) {
            return "A fair result for the term. The pupil has the ability to achieve more with increased focus and participation in class. Let's work on building confidence.";
        } else {
            return "More effort is required across all learning areas. Please ensure all assignments are completed and encourage participation at home. I am here to help.";
        }
    }
}

if (!function_exists('generateNurseryHeadTeacherRemarkUtil')) {
    function generateNurseryHeadTeacherRemarkUtil($averageScore) {
        if ($averageScore === null || !is_numeric($averageScore)) {
            return "The pupil's performance could not be determined. It is important to participate in all assessments.";
        }

        if ($averageScore >= 90) {
            return "A truly excellent result. It is clear this pupil is a bright and engaged learner. We are very proud of this achievement.";
        } elseif ($averageScore >= 80) {
            return "This is a very strong performance. The pupil has demonstrated great potential. The school encourages this continued dedication.";
        } elseif ($averageScore >= 60) {
            return "A commendable effort this term. We are pleased with the progress being made. Keep up the good work.";
        } elseif ($averageScore >= 40) {
            return "There is potential for improvement. The school encourages more consistent application to studies next term.";
        } else {
            return "A significant improvement is needed next term. The school expects more dedication to learning activities. Please see the class teacher for support.";
        }
    }
}

if (!function_exists('getNurseryGradeFromScoreUtil')) {
    function getNurseryGradeFromScoreUtil($score) {
        if ($score === null || !is_numeric($score)) {
            return 'N/A';
        }
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 40) return 'D';
        return 'E';
    }
}

if (!function_exists('getNurserySubjectRemarkFromScoreUtil')) {
    function getNurserySubjectRemarkFromScoreUtil($score) {
        if ($score === null || !is_numeric($score)) {
            return 'N/A';
        }
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'V.Good';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Fair';
        return 'Put more efforts';
    }
}

?>
