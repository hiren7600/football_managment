<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

#[Route('/team')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request ,PersistenceManagerRegistry $doctrine): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
                
            $image = $request->files->get('team')['logo'];

            if($image){
                $dateiname = md5(uniqid()) . '.' . $image->guessClientExtension();
            }

            $image->move(
                $this->getParameter('teams'),
                $dateiname
            );
           
            $team->setLogo($dateiname);

            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team ,int $id ,PlayerRepository $playerRepository): Response
    {
        
        $teamPlayers = $playerRepository->findPlayersByTeamId($id);
       
        return $this->render('team/show.html.twig', [
            'team' => $team,
            'teamPlayers'=>$teamPlayers
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, TeamRepository $teamRepository, PersistenceManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
                
            $image = $request->files->get('team')['logo'];

            if($image){
                $dateiname = md5(uniqid()) . '.' . $image->guessClientExtension();
            }

            $image->move(
                $this->getParameter('teams'),
                $dateiname
            );
           
            $team->setLogo($dateiname);

            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('team/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, TeamRepository $teamRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            try {
                $teamRepository->remove($team, true);
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $th) {
            }
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }
}
