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
    /**
     * @test
     */
    public function testStatsForOneMonth(): void
    {
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2018-08-01 00:00:00');
        $endDate   = DateTime::createFromFormat('Y-m-d H:i:s', '2018-08-31 23:59:59');
        $params = [
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
        ];

        $posts = $this->fetchPosts();
        $statsService = StatisticsServiceFactory::create();
        $stats = $statsService->calculateStats($posts, $params);

        $noopStats = $stats->getChildren();
        $allMonthsStats = $noopStats[0]->getChildren();
        $monthStats = $allMonthsStats[0];
        $averagePostsPerUserPerMonth = $monthStats->getValue();

        $this->assertEquals(1, $averagePostsPerUserPerMonth);
    }

    private function fetchPosts() : Traversable {
        $hydrator = new FictionalPostHydrator();

        $postsJson = file_get_contents('./tests/data/social-posts-response.json');
        $responseData = json_decode($postsJson, true);
        $posts = $responseData['data']['posts'];

        foreach ($posts as $postData) {
                yield $hydrator->hydrate($postData);
            }
    }
}
