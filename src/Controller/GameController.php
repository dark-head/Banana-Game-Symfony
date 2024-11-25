<?php

namespace App\Controller;

use App\Constant\Constant;
use App\Entity\GameSetting;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\GameSession;

class GameController extends AbstractController
{

    protected EntityManagerInterface $entityManager;
    protected string $API_URL = 'http://marcconrad.com/uob/banana/api.php';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

//    #[Route('/game', name: 'game_home')]
//    public function index(): Response
//    {
//        return $this->render('game/index.html.twig');
//    }

    #[Route('/game/level/{gameSetting}', name: 'game_level')]
    public function playLevel(Request $request, GameSetting $gameSetting): Response
    {
        // Define time limits per level
        $timeLimits = $gameSetting->getTotalSecond();

        $level = $gameSetting->getLevel();

        // Fetch question from the API
        $apiUrl = $this->API_URL;
        $questionData = json_decode(file_get_contents($apiUrl), true);

        // Access the session via the request
        $session = $request->getSession();

        // Now you can use the session as needed
        if (!$session->has('game_state')) {
            $session->set('game_state', [
                'score' => 0,
                'lives' => 3,
            ]);
        }
        $session->set('level', $level);
        $session->set('gameSetting', $gameSetting);
        return $this->render('game/play.html.twig', [
            'level' => $level,
            'difficulty' => Constant::$gameLevels[$level],
            'question' => $questionData['question'],
            'solution' => $questionData['solution'],
            'timeLimit' => $timeLimits,
            'gameState' => $session->get('game_state'),
        ]);
    }

    #[Route('/game/submit-answer', name: 'game_submit_answer', methods: ['POST'])]
    public function submitAnswer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $answer = $data['answer'] ?? null;
        $solution = $data['solution'] ?? null;
        $session = $request->getSession();
        $gameState = $session->get('game_state');
        $gameSetting = $session->get('gameSetting');

        // Check answer and update score or lives
        if ($answer == $solution) {
            $gameState['score']++;
        } else {
            $gameState['lives']--;
        }
        // End game if no lives left
        if ($gameState['lives'] <= 0) {
            // Save game data to database
            $this->saveGameData($gameState, $gameSetting);
            $score = $gameState['score'];
            $session->remove('game_state'); // Reset for new game
            return new JsonResponse(['gameOver' => true, 'score' => $score]);
        }

        $session->set('game_state', $gameState);
        return new JsonResponse(['score' => $gameState['score'], 'lives' => $gameState['lives']]);
    }

    private function saveGameData(array $gameState, GameSetting $gameSetting): void
    {
        $gameSession = new GameSession();
        $gameSession->setScore($gameState['score']);
        $gameSession->setDate(new \DateTime());
        $gameSession->setGameSetting($gameSetting);
        $gameSession->setUser($this->getUser());
        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();
    }
}
