<?php

declare(strict_types = 1);

namespace Tests\unit;

use Traversable;
use DateTime;
use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use Statistics\Service\Factory\StatisticsServiceFactory;
use SocialPost\Hydrator\FictionalPostHydrator;

/**
 * Class ATestAveragePostsPerUserPerMonth
 *
 * @package Tests\unit
 */
class AveragePostsPerUserPerMonthTest extends TestCase
{
    private $params;

    protected function setUpBeforeClass(): void
    {
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2018-08-01 00:00:00');
        $endDate   = DateTime::createFromFormat('Y-m-d H:i:s', '2018-08-31 23:59:59');
        $this->params = [
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
        ];
    }

    /**
     * @test
     */
    public function testStatsForOneMonth(): void
    {
        $postsJson = file_get_contents('./tests/data/social-posts-response.json');
        $responseData = json_decode($postsJson, true);

        $posts = $this->fetchPosts($responseData['data']['posts']);
        $statsService = StatisticsServiceFactory::create();
        $stats = $statsService->calculateStats($posts, $this->params);

        $averageStats = $stats->getChildren();
        $allMonthsStats = $averageStats[0]->getChildren();
        $monthStats = $allMonthsStats[0];
        $averagePostsPerUserPerMonth = $monthStats->getValue();

        $this->assertEquals(1, $averagePostsPerUserPerMonth);
    }

    /**
     * @test
     */
    public function testForNoPosts(): void
    {
        $posts = $this->fetchPosts([]);
        $statsService = StatisticsServiceFactory::create();
        $stats = $statsService->calculateStats($posts, $this->params);

        $averageStats = $stats->getChildren();
        $allMonthsStats = $averageStats[0]->getChildren();

        $this->assertEmpty($allMonthsStats);
    }

    private function fetchPosts($posts) : Traversable {
        $hydrator = new FictionalPostHydrator();

        foreach ($posts as $postData) {
                yield $hydrator->hydrate($postData);
            }
    }
}
