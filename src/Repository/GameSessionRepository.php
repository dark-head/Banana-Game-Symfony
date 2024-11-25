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
}
