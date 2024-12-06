<?php

namespace App\Controller;

use App\Constant\Constant;
use App\Entity\GameSetting;
use App\Entity\GameSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]
class GameController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; // Injecting EntityManager to handle database operations
    }

    #[Route('/level/{gameSetting}', name: 'game_level')]
    public function playLevel(Request $request, GameSetting $gameSetting): Response
    {
        // Initialize game state in the session if not already present
        $session = $request->getSession();
        $this->initializeGameState($session, $gameSetting);

        // Fetch the question for the game
        $questionData = $this->fetchQuestion();

        if (is_null($questionData)) {
            return $this->renderErrorPage('Unable to fetch the question. Please try again later.');
        }

        return $this->render('game/play.html.twig', [
            'level' => $gameSetting->getLevel(),
            'difficulty' => Constant::$gameLevels[$gameSetting->getLevel()],
            'question' => $questionData['question'],
            'solution' => $questionData['solution'],
            'timeLimit' => $gameSetting->getTotalSecond(),
            'gameState' => $session->get('game_state'),
        ]);
    }

    #[Route('/submit-answer', name: 'game_submit_answer', methods: ['POST'])]
    public function submitAnswer(Request $request): JsonResponse
    {
        // Decode the incoming answer data from the request
        $data = json_decode($request->getContent(), true);
        $answer = $data['answer'] ?? null;
        $solution = $data['solution'] ?? null;

        // Validate the answer format
        if (!is_numeric($answer) && $answer !== null) {
            return $this->json(['error' => 'Invalid answer format'], Response::HTTP_BAD_REQUEST);
        }

        // Get the session and current game state
        $session = $request->getSession();
        $gameState = $session->get('game_state', ['score' => 0, 'lives' => 3]);
        $gameSettingId = $session->get('gameSetting');

        // Update the game state based on the user's answer
        $this->updateGameState($gameState, $answer, $solution);

        // End the game if lives are 0
        if ($gameState['lives'] <= 0) {
            $this->saveGameData($gameState, $gameSettingId);
            $session->remove('game_state');
            return $this->json(['gameOver' => true, 'score' => $gameState['score']]);
        }

        // Fetch a new question
        $questionData = $this->fetchQuestion();
        if (!$questionData) {
            return $this->json(['error' => 'Unable to fetch new question'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $session->set('game_state', $gameState);

        return $this->json([
            'score' => $gameState['score'],
            'lives' => $gameState['lives'],
            'newQuestionImage' => $questionData['question'],
            'solution' => $questionData['solution'],
        ]);
    }

    // Initialize the game state in the session with default values
    private function initializeGameState($session, GameSetting $gameSetting): void
    {
        if (!$session->has('game_state')) {
            $session->set('game_state', ['score' => 0, 'lives' => 3]);
        }
        $session->set('level', $gameSetting->getLevel());
        $session->set('gameSetting', $gameSetting->getId());
    }

    // Fetch the next question from an external API
    private function fetchQuestion()
    {
        try {
            $response = file_get_contents(Constant::API_URL); // External API call
            $questionData = json_decode($response, true);
            if (isset($questionData['question'], $questionData['solution'])) {
                return $questionData; // Return the question and solution if valid
            }
        } catch (\Exception $e) {
            return null; // Return null if API call fails
        }

        return null; // Return null if the data is invalid
    }

    // Update the game state based on whether the answer was correct
    private function updateGameState(array &$gameState, ?string $answer, ?string $solution): void
    {
        if ($answer === $solution) {
            $gameState['score']++; // Increase score if the answer is correct
        } else {
            $gameState['lives']--; // Decrease lives if the answer is incorrect
        }
    }

    // Save the game session data into the database
    private function saveGameData(array $gameState, int $gameSettingId): void
    {
        $gameSetting = $this->entityManager->find(GameSetting::class, $gameSettingId);
        if (!$gameSetting) {
            return; // Optionally log this error if GameSetting is not found
        }

    // Create a new game session entry
        $gameSession = (new GameSession())
            ->setScore($gameState['score'])
            ->setDate(new \DateTime())
            ->setGameSetting($gameSetting)
            ->setUser($this->getUser()); // Assign the current user to the session

    // Persist the game session into the database
        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();
    }

    // Render an error page with a specific message
    private function renderErrorPage(string $message): Response
    {
        return $this->render('error.html.twig', ['message' => $message]);
    }
}
