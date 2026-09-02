<?php

namespace App\Controller\Admin;

use App\Entity\Conge;
use App\Exception\TransitionInvalideException;
use App\Form\CongeReponseType;
use App\Repository\CongeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Exception\LogicException as WorkflowLogicException;
use Symfony\Component\Workflow\Registry;

/**
 * Vue globale RH/Admin sur toutes les demandes de congé, tous services
 * confondus — avec possibilité de traiter directement une demande en
 * attente si besoin (ex. absence du responsable habituel).
 */
#[Route('/admin/conges')]
#[IsGranted('ROLE_RH')]
class CongeAdminController extends AbstractController
{
    #[Route('', name: 'admin_conge_index', methods: ['GET'])]
    public function index(Request $request, CongeRepository $repository): Response
    {
        $statut = $request->query->get('statut');

        return $this->render('admin/conge/index.html.twig', [
            'conges' => $statut
                ? $repository->findBy(['statut' => $statut], ['dateDebut' => 'DESC'])
                : $repository->findBy([], ['dateDebut' => 'DESC']),
            'statutFiltre' => $statut,
        ]);
    }

    #[Route('/{id}/repondre', name: 'admin_conge_repondre', methods: ['GET', 'POST'])]
    #[IsGranted('CONGE_VALIDER', subject: 'conge')]
    public function repondre(
        Conge $conge,
        Request $request,
        EntityManagerInterface $em,
        Registry $workflowRegistry,
        LoggerInterface $logger
    ): Response {
        $form = $this->createForm(CongeReponseType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $decision = $form->get('decision')->getData();
                $workflow = $workflowRegistry->get($conge, 'conge');

                if (!$workflow->can($conge, $decision)) {
                    throw new TransitionInvalideException(
                        'Cette demande a déjà été traitée ou ne peut plus être modifiée.'
                    );
                }

                $conge->setValidateur($this->getUser());
                $workflow->apply($conge, $decision);
                $em->flush();

                $this->addFlash('success', 'La demande a été traitée.');
            } catch (TransitionInvalideException|WorkflowLogicException $e) {
                $this->addFlash('error', $e->getMessage() ?: 'Cette demande ne peut plus être traitée.');
            } catch (\Throwable $e) {
                $logger->error('Échec inattendu lors du traitement admin d\'une demande de congé', [
                    'exception' => $e,
                    'conge_id' => $conge->getId(),
                ]);
                $this->addFlash('error', 'Une erreur est survenue, veuillez réessayer.');
            }

            return $this->redirectToRoute('admin_conge_index');
        }

        return $this->render('admin/conge/repondre.html.twig', ['conge' => $conge, 'form' => $form]);
    }
}
