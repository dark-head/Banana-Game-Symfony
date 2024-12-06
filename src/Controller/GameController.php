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
    private const API_URL = 'http://marcconrad.com/uob/banana/api.php';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/level/{gameSetting}', name: 'game_level')]
    public function playLevel(Request $request, GameSetting $gameSetting): Response
    {
        $session = $request->getSession();
        $this->initializeGameState($session, $gameSetting);

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
        $data = json_decode($request->getContent(), true);
        $answer = $data['answer'] ?? null;
        $solution = $data['solution'] ?? null;

        if (!is_numeric($answer) && $answer !== null) {
            return $this->json(['error' => 'Invalid answer format'], Response::HTTP_BAD_REQUEST);
        }

        $session = $request->getSession();
        $gameState = $session->get('game_state', ['score' => 0, 'lives' => 3]);
        $gameSettingId = $session->get('gameSetting');

        $this->updateGameState($gameState, $answer, $solution);

        if ($gameState['lives'] <= 0) {
            $this->saveGameData($gameState, $gameSettingId);
            $session->remove('game_state');
            return $this->json(['gameOver' => true, 'score' => $gameState['score']]);
        }

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

    private function initializeGameState($session, GameSetting $gameSetting): void
    {
        if (!$session->has('game_state')) {
            $session->set('game_state', ['score' => 0, 'lives' => 3]);
        }
        $session->set('level', $gameSetting->getLevel());
        $session->set('gameSetting', $gameSetting->getId());
    }

    private function fetchQuestion()
    {
        try {
            $response = file_get_contents(self::API_URL);
            $questionData = json_decode($response, true);
            if (isset($questionData['question'], $questionData['solution'])) {
                return $questionData;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    private function updateGameState(array &$gameState, ?string $answer, ?string $solution): void
    {
        if ($answer === $solution) {
            $gameState['score']++;
        } else {
            $gameState['lives']--;
        }
    }

    private function saveGameData(array $gameState, int $gameSettingId): void
    {
        $gameSetting = $this->entityManager->find(GameSetting::class, $gameSettingId);
        if (!$gameSetting) {
            return; // Optionally log this error
        }

        $gameSession = (new GameSession())
            ->setScore($gameState['score'])
            ->setDate(new \DateTime())
            ->setGameSetting($gameSetting)
            ->setUser($this->getUser());

        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();
    }

    private function renderErrorPage(string $message): Response
    {
        return $this->render('error.html.twig', ['message' => $message]);
    }
}
