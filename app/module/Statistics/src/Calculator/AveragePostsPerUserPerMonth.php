<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $postTotals = [];

    /**
     * @var array
     */
    private $userList = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getDate()->format('M, Y');

        $this->postTotals[$key] = ($this->postTotals[$key] ?? 0) + 1;

        if (!in_array($postTo->getAuthorId(), $this->userList)) {
            $this->userList[] = $postTo->getAuthorId();
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $averagePostsPerUserPerMonth = 0;
        $totalPosts = array_sum($this->postTotals);
        if ($totalPosts > 0) {
            $numberOfMonths = count($this->postTotals);
            $numberOfAuthors = count($this->userList);
            $averagePostsPerUserPerMonth = round($totalPosts / $numberOfAuthors / $numberOfMonths, 1);
        }

        return (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setValue($averagePostsPerUserPerMonth)
                ->setUnits(self::UNITS);
    }
}
