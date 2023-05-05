<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="app_dashboard")
     */
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('dash_team/{id}', name: 'team_details', methods: ['GET'])]
    public function show(Team $team ,int $id ,PlayerRepository $playerRepository): Response
    {
        $teamPlayers = $playerRepository->findPlayersByTeamId($id);
       
        return $this->render('dashboard/details.html.twig', [
            'team' => $team,
            'teamPlayers'=>$teamPlayers
        ]);
    }
}
