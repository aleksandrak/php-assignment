<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{

    protected const UNITS = 'posts per user';

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

        $this->userList[$key] = $this->userList[$key] ?? [];
        if (!in_array($postTo->getAuthorId(), $this->userList[$key])) {
            $this->userList[$key][] = $postTo->getAuthorId();
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $averages = [];
        foreach ($this->postTotals as $key => $postTotal) {
            $authorCount = count($this->userList[$key]);
            $averages[$key] = round($postTotal / $authorCount, 1);
        }

        $stats = new StatisticsTo();
        foreach ($$averages as $splitPeriod => $average) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($splitPeriod)
                ->setValue($average)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
