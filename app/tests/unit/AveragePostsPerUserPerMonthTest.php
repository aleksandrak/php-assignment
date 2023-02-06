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
    protected const TEST_FILE = './tests/data/average-posts-per-user-per-month-data.json';

    /**
     * @test
     */
    public function testStatsForOneMonth(): void
    {
        $posts = $this->getTestPosts();
        $params = $this->getParams('2018-08-01 00:00:00', '2018-08-31 23:59:59');
        
        $averagePostsPerUserPerMonth = $this->getAveragePostsPerUserPerMonth($posts, $params);

        $this->assertEquals(1, $averagePostsPerUserPerMonth);
    }

    /**
     * @test
     */
    public function testStatsForTwoMonths(): void
    {
        $posts = $this->getTestPosts();
        $params = $this->getParams('2018-08-01 00:00:00', '2018-09-30 23:59:59');

        $averagePostsPerUserPerMonth = $this->getAveragePostsPerUserPerMonth($posts, $params);

        $this->assertEquals(0.7, $averagePostsPerUserPerMonth);
    }

    /**
     * @test
     */
    public function testForNoPosts(): void
    {
        $posts = [];
        $params = $this->getParams('2018-08-01 00:00:00', '2018-08-31 23:59:59');
        
        $averagePostsPerUserPerMonth = $this->getAveragePostsPerUserPerMonth($posts, $params);

        $this->assertEquals(0, $averagePostsPerUserPerMonth);
    }
    
    private function getTestPosts()
    {
        $postsJson = file_get_contents(self::TEST_FILE);
        $responseData = json_decode($postsJson, true);

        return $responseData['data']['posts'];
    }
    
    private function getAveragePostsPerUserPerMonth($posts, $params)
    {
        $posts = $this->fetchPosts($posts);
        $stats = $this->getStats($params, $posts);

        return $stats->getChildren()[0]->getValue();
    }

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

    private function fetchPosts($posts) : Traversable {
        $hydrator = new FictionalPostHydrator();

        foreach ($posts as $postData) {
                yield $hydrator->hydrate($postData);
            }
    }
}
