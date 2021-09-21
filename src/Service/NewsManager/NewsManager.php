<?php


namespace App\Service\NewsManager;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;

class NewsManager implements NewsManagerInterface
{
    public const TABLES = [
        'wp_posts' => 'post_content, post_title',
        'wp1of20_posts' => 'post_content, post_title'
    ];

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection
    )
    {

        $this->connection = $connection;
    }

    public function findNews(): array
    {
        $news = [];

        foreach (self::TABLES as $table => $fields){
            $queryBuilder = $this->connection->createQueryBuilder();

            $queryBuilder->select($fields)
                ->from($table)
                ->andWhere("post_title != ''")
                ->andWhere("post_content != ''")
            ;

            try {
                $newsFromDB = $queryBuilder->execute()->fetchAllAssociative();

                $news[$table] = array_map([$this,'clearData'],$newsFromDB);
            }catch (TableNotFoundException $exception){
                //do something
            }
        }

        return $news;
    }

    private function clearData(array $newsFromDB):array
    {
        return [
            'post_content' => preg_replace('#<a.*?>(.*?)</a>#i', '\1', $newsFromDB['post_content']),
            'post_title' => preg_replace('#<a.*?>(.*?)</a>#i', '\1', $newsFromDB['post_title'])
        ];
    }
}