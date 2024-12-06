<?php

namespace App\Controller;

use App\Entity\GameSetting;
use App\Form\GameSettingFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game-setting')]

class GameSettingController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_game_setting')]
    public function list(Request $request): Response
    {
        $gameSetting = $this->entityManager->getRepository(GameSetting::class)->findAll();
        return $this->render('game_setting/list.html.twig', ['gameSetting' => $gameSetting]);
    }

    #[Route('/create', name: 'game_setting_new')]
    #[Route('/edit/{id}', name: 'game_setting_edit')]
    public function new(Request $request): Response
    {
        if ($request->get('id')) {
            $gameSetting = $this->entityManager->getRepository(GameSetting::class)->find($request->get('id'));
        } else {
            $gameSetting = new GameSetting();
        }

        $form = $this->createForm(GameSettingFormType::class, $gameSetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->entityManager->persist($formData);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_game_setting');
        }

        return $this->render('game_setting/new.html.twig', [
            'form' => $form,
        ]);
    }
}