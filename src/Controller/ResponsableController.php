<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Entity\Utilisateur;
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

#[Route('/responsable')]
#[IsGranted('ROLE_RESPONSABLE')]
class ResponsableController extends AbstractController
{
    #[Route('', name: 'responsable_index', methods: ['GET'])]
    public function index(CongeRepository $congeRepository): Response
    {
        /** @var Utilisateur $responsable */
        $responsable = $this->getUser();

        return $this->render('responsable/index.html.twig', [
            'conges' => $congeRepository->findEnAttentePourResponsable($responsable),
        ]);
    }

    #[Route('/{id}/repondre', name: 'responsable_repondre', methods: ['GET', 'POST'])]
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
                $decision = $this->appliquerDecision($conge, $form->get('decision')->getData(), $workflowRegistry);
                $em->flush();

                $this->addFlash('success', 'La demande a été traitée.');

                return $this->redirectToRoute('responsable_index');
            } catch (TransitionInvalideException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable $e) {
                $logger->error('Échec inattendu lors du traitement d\'une demande de congé', [
                    'exception' => $e,
                    'conge_id' => $conge->getId(),
                ]);
                $this->addFlash('error', 'Une erreur est survenue, veuillez réessayer.');
            }
        }

        return $this->render('responsable/repondre.html.twig', [
            'conge' => $conge,
            'form' => $form,
        ]);
    }

    /**
     * @throws TransitionInvalideException
     */
    private function appliquerDecision(Conge $conge, string $decision, Registry $workflowRegistry): void
    {
        $workflow = $workflowRegistry->get($conge, 'conge');

        if (!$workflow->can($conge, $decision)) {
            throw new TransitionInvalideException(
                'Cette demande a déjà été traitée ou ne peut plus être modifiée.'
            );
        }

        $conge->setValidateur($this->getUser());

        try {
            $workflow->apply($conge, $decision);
        } catch (WorkflowLogicException $e) {
            throw new TransitionInvalideException(
                'Cette demande a déjà été traitée ou ne peut plus être modifiée.',
                previous: $e
            );
        }
    }
}
