<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface FraudScreenRuleInterface
{
    const DESCRIPTION = 'description';
    const SCORE       = 'score';

    /**
     * T3M.
     * @return string
     */
    public function getDescription();

    /**
     * T3M.
     * @return int
     */
    public function getScore();

    /**
     * @param $score
     * @return void
     */
    public function setScore($score);

    /**
     * @param $description
     * @return void
     */
    public function setDescription($description);
}
