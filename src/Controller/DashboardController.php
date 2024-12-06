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

    // Injecting the EntityManager to interact with the database
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        // Initialize the game state with default values
        $session->set('game_state', [
            'score' => 0,
            'lives' => 3,
        ]);
        $session->set('difficulty', '');

        // Fetch game settings for each difficulty level
        $easy = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::EASY_LEVEL]);
        $medium = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::MEDIUM_LEVEL]);
        $hard = $this->entityManager->getRepository(GameSetting::class)->findOneBy(['level' => Constant::HARD_LEVEL]);

        // Organize and get the highest score for each difficulty level
        $highestScores = [
            'easy' => $this->getHighestScore(Constant::EASY_LEVEL),
            'medium' => $this->getHighestScore(Constant::MEDIUM_LEVEL),
            'hard' => $this->getHighestScore(Constant::HARD_LEVEL),
        ];

        // Render the dashboard with the highest scores and game settings for each level
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'highestScores' => $highestScores,
            'easy' => $easy->getId(),
            'medium' => $medium->getId(),
            'hard' => $hard->getId(),
        ]);
    }

    // Function to get the highest score for a given difficulty level
    private function getHighestScore(int $difficulty): int
    {
        $params['user'] = $this->getUser()->getId();
        $params['level'] = $difficulty;
        $params['highestScore'] = true;

        // Query to get the user's highest score for the specified difficulty level
        $highestScore = $this->entityManager->getRepository(GameSession::class)
            ->getAllQuery($params)->getQuery()->getResult();

        return $highestScore ? $highestScore[0]->getScore() : 0; // Return the score if found, else return 0
    }

    #[Route('/user/{id}/game-sessions', name: 'user_game_sessions')]
    public function userGameSessions($id): Response
    {
        // Fetch game sessions of the user with the given ID
        $gameSessions = $this->entityManager->getRepository(GameSession::class)->findByUser($id);

        // Render the game sessions history for the user
        return $this->render('dashboard/history.html.twig', [
            'gameSessions' => $gameSessions,
        ]);
    }

    #[Route('/leaderboards', name: 'leaderboard')]
    public function leaderboard(): Response
    {
        // Fetch the leaderboard for each difficulty level
        $easyLevel = Constant::EASY_LEVEL;
        $mediumLevel = Constant::MEDIUM_LEVEL;
        $hardLevel = Constant::HARD_LEVEL;

        $easyLeaderboard = $this->entityManager->getRepository(GameSession::class)->findTopUsersByLevel($easyLevel);
        $mediumLeaderboard = $this->entityManager->getRepository(GameSession::class)->findTopUsersByLevel($mediumLevel);
        $hardLeaderboard = $this->entityManager->getRepository(GameSession::class)->findTopUsersByLevel($hardLevel);

        // Organize the scores by difficulty level
        $scoresByLevel = [
            'easy' => $easyLeaderboard,
            'medium' => $mediumLeaderboard,
            'hard' => $hardLeaderboard,
        ];

        // Render the leaderboard page with scores categorized by difficulty level
        return $this->render('dashboard/leaderboard.html.twig', [
            'scoresByLevel' => $scoresByLevel,
        ]);
    }
}

