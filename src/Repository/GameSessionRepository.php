<?php

namespace App\Repository;

use App\Entity\GameSession;
use App\Helper\QueryHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class GameSessionRepository extends EntityRepository
{
    public function getAllQuery(array $params = []): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('gs')
            ->from(GameSession::class, 'gs')
            ->leftJoin('gs.gameSetting', 'game_setting');

        if (QueryHelper::FilterCheck($params, 'level'))
            $qb->andWhere('game_setting.level = :level')->setParameter('level', $params['level']);

        if (QueryHelper::FilterCheck($params, 'user'))
            $qb->andWhere('gs.user = :user')->setParameter('user', $params['user']);


//        if (QueryHelper::FilterCheck($params, 'highestScore', true))
//            $qb->andWhere('gs.score = MAX(gs.score)');
//        dd($params);

        $qb->orderBy('gs.id', 'DESC');

        return $qb;
    }

    public function findByUser($userId)
    {
        return $this->createQueryBuilder('gs')
            ->innerJoin('gs.gameSetting', 'g')
            ->addSelect('g')
            ->where('gs.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('g.level', 'DESC')
            ->orderBy('gs.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopUsersByLevel(int $level, int $limit = 20): array
    {
        return $this->createQueryBuilder('gs')
            ->select('u.username, MAX(gs.score) AS highestScore, MIN(gs.date) AS firstAchieved')
            ->join('gs.user', 'u')
            ->join('gs.gameSetting', 'g')
            ->where('g.level = :level')
            ->setParameter('level', $level)
            ->groupBy('u.id')
            ->orderBy('highestScore', 'DESC') // Sort by highest score
            ->setMaxResults($limit)          // Limit to the top 20 users
            ->getQuery()
            ->getResult();
    }
}
