<?php

namespace App\Controller;

use App\Constant\Constant;
use App\Entity\GameSession;
use App\Entity\GameSetting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        $session->set('game_state', [
            'score' => 0,
            'lives' => 3,
        ]);
        $session->set('difficulty', '');


        $easy = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::EASY_LEVEL]);
        $medium = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::MEDIUM_LEVEL]);
        $hard = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::HARD_LEVEL]);
        $highestScores = [
            'easy' => $this->getHighestScore(Constant::EASY_LEVEL),
            'medium' => $this->getHighestScore(Constant::MEDIUM_LEVEL),
            'hard' => $this->getHighestScore(Constant::HARD_LEVEL),
        ];
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'highestScores' => $highestScores,
            'easy' => $easy->getId(),
            'medium' => $medium->getId(),
            'hard' => $hard->getId(),
        ]);
    }

    private function getHighestScore(int $difficulty): int
    {
        $params['user'] = $this->getUser()->getId();
        $params['level'] = $difficulty;
        $params['highestScore'] = true;
        // Get user the highest score per difficulty
        $highestScore = $this->entityManager->getRepository(GameSession::class)
            ->getAllQuery($params)->getQuery()->getOneOrNullResult();
        return $highestScore ? $highestScore->getScore() : 0;
    }

    private function getUserHistory(): GameSession
    {
        // Get user history
        /** @var GameSession $userHistory */
        $userHistory = $this->entityManager->getRepository(GameSession::class)
            ->findBy(['user' => $this->getUser()], ['score' => 'DESC']);

        return $userHistory;
    }
}
