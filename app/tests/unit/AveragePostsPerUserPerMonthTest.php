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

    private function getParams($start, $end): array
    {
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $start);
        $endDate   = DateTime::createFromFormat('Y-m-d H:i:s', $end);
        return [
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
        ];
    }
    
    private function getStats($params, $posts)
    {
        $statsService = StatisticsServiceFactory::create();
        return $statsService->calculateStats($posts, $params);
    }

    /**
     * @test
     */
    public function testStatsForOneMonth(): void
    {
        $postsJson = file_get_contents('./tests/data/average-posts-per-user-per-month-data.json');
        $responseData = json_decode($postsJson, true);

        $params = $this->getParams('2018-08-01 00:00:00', '2018-08-31 23:59:59');
        $posts = $this->fetchPosts($responseData['data']['posts']);
        $stats = $this->getStats($params, $posts);

        $averageStats = $stats->getChildren();
        $allMonthsStats = $averageStats[0]->getChildren();
        $monthStats = $allMonthsStats[0];
        $averagePostsPerUserPerMonth = $monthStats->getValue();

        $this->assertEquals(1, $averagePostsPerUserPerMonth);
    }

    /**
     * @test
     */
    public function testRoundingStatNumber(): void
    {
        $postsJson = file_get_contents('./tests/data/average-posts-per-user-per-month-data.json');
        $responseData = json_decode($postsJson, true);

        $params = $this->getParams('2018-09-01 00:00:00', '2018-09-30 23:59:59');
        $posts = $this->fetchPosts($responseData['data']['posts']);
        $stats = $this->getStats($params, $posts);

        $averageStats = $stats->getChildren();
        $allMonthsStats = $averageStats[0]->getChildren();
        $monthStats = $allMonthsStats[0];
        $averagePostsPerUserPerMonth = $monthStats->getValue();
        $this->assertEquals(1.5, $averagePostsPerUserPerMonth);
    }

    /**
     * @test
     */
    public function testForNoPosts(): void
    {
        $params = $this->getParams('2018-08-01 00:00:00', '2018-08-31 23:59:59');
        $posts = $this->fetchPosts([]);
        $stats = $this->getStats($params, $posts);

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
