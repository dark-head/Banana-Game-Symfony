<?php

namespace App\Controller;

use App\Entity\GameSetting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GameSettingController extends AbstractController
{
    protected EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function list(Request $request): Response
    {
        $gameSetting = $this->entityManager->getRepository(GameSetting::class)->findAll();
        return $this->render('game_setting/list.html.twig', [$gameSetting]);
    }
}