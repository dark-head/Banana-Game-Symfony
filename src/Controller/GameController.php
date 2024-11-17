<?php

namespace App\Controller;

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

    #[Route('/game', name: 'game_home')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/level/{difficulty}', name: 'game_level')]
    public function playLevel(Request $request, string $difficulty): Response
    {
        // Define time limits per level
        $timeLimits = [
            'easy' => 60,
            'medium' => 30,
            'hard' => 15,
        ];

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
                'highestScore' => 0,
            ]);
        }

        return $this->render('game/play.html.twig', [
            'difficulty' => $difficulty,
            'question' => $questionData['question'],
            'solution' => $questionData['solution'],
            'timeLimit' => $timeLimits[$difficulty],
            'gameState' => $session->get('game_state'),
        ]);
    }

    #[Route('/game/submit-answer', name: 'game_submit_answer', methods: ['POST'])]
    public function submitAnswer(Request $request): JsonResponse
    {
        $answer = $_POST['answer'];
        $solution = $_POST['solution'];
        $session = $request->getSession();
        $gameState = $session->get('game_state');

        // Check answer and update score or lives
        if ($answer == $solution) {
            $gameState['score']++;
        } else {
            $gameState['lives']--;
        }
//        dd($gameState, $answer, $solution);
        // End game if no lives left
        if ($gameState['lives'] <= 0) {
            $gameState['highestScore'] = max($gameState['highestScore'], $gameState['score']);
            // Save game data to database
            $this->saveGameData($gameState);
            $session->remove('game_state'); // Reset for new game
            return new JsonResponse(['gameOver' => true, 'highestScore' => $gameState['highestScore']]);
        }

        $session->set('game_state', $gameState);
        return new JsonResponse(['score' => $gameState['score'], 'lives' => $gameState['lives']]);
    }

    private function saveGameData(array $gameState): void
    {
        $gameSession = new GameSession();
        $gameSession->setScore($gameState['highestScore']);
        $gameSession->setDate(new \DateTime());
        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();
    }
}
