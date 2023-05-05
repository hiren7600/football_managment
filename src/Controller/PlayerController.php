<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Form\ChangePlayerTeamType;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/player')]
class PlayerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_player_index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository): Response
    {
        return $this->render('player/index.html.twig', [
            'players' => $playerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_player_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        PersistenceManagerRegistry $doctrine
    ): Response {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Entity Manager 
            $em = $doctrine->getManager();

            $image = $request->files->get('player')['image'];

            if ($image) {
                $dateiname = md5(uniqid()) . '.' . $image->guessClientExtension();
            }

            $image->move(
                $this->getParameter('players'),
                $dateiname
            );

            $player->setImage($dateiname);

            $em->persist($player);
            $em->flush();

            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('player/new.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_player_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Player $player, PersistenceManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Entity Manager 
            $em = $doctrine->getManager();

            $image = $request->files->get('player')['image'];

            if ($image) {
                $dateiname = md5(uniqid()) . '.' . $image->guessClientExtension();
            }

            $image->move(
                $this->getParameter('players'),
                $dateiname
            );

            $player->setImage($dateiname);

            $em->persist($player);
            $em->flush();

            // return $this->redirect($this->generateUrl('app_player_index'));
            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('player/edit.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/changeteam', name: 'app_player_addtoteam', methods: ['GET', 'POST'])]
    public function addToTeam(Request $request, Player $player, int $id, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ChangePlayerTeamType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $team = $request->get('player')->getTeam();
            $player->setTeam($team);
            $em->persist($player);
            $em->flush();
            return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('player/changeteam.html.twig', [
            'player' => $player,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_player_delete', methods: ['POST'])]
    public function delete(Request $request, Player $player, PlayerRepository $playerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->request->get('_token'))) {
            $playerRepository->remove($player, true);
        }

        return $this->redirectToRoute('app_player_index', [], Response::HTTP_SEE_OTHER);
    }
}
