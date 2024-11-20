<?php

namespace App\Controller;

use App\Entity\GameSession;
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


        $highestScores = [
            'easy' => $this->getHighestScore('easy'),
            'medium' => $this->getHighestScore('medium'),
            'hard' => $this->getHighestScore('hard'),
        ];
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'highestScores' => $highestScores,
        ]);
    }

    private function getHighestScore(string $difficulty): int
    {
        // Get user highest score per difficulty
        $highestScore = $this->entityManager->getRepository(GameSession::class)
            ->findOneBy(['difficulty' => $difficulty, 'user' => $this->getUser()], ['score' => 'DESC']);

        return $highestScore ? $highestScore->getScore() : 0;
    }

    private function getUserHistory(string $difficulty): int
    {
        // Get user history
        $userHistory = $this->entityManager->getRepository(GameSession::class)
            ->findBy(['user' => $this->getUser()], ['score' => 'DESC']);

        return $userHistory ? $userHistory->getScore() : 0;
    }
}
