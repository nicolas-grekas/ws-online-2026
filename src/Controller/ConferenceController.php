<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class ConferenceController extends AbstractController
{
    public function __construct(
        #[AutowireMethodOf(ControllerHelper::class)]
        private \Closure $render,

        #[AutowireCallable(service: ConferenceRepository::class, method: 'findAll')]
        private \Closure $findAllConferences,

        #[AutowireMethodOf(CommentRepository::class)]
        private \Closure $getCommentPaginator,
    ) {
    }

    #[Route('/', name: 'homepage')]
    public function index(
    ): Response
    {
        return ($this->render)('conference/index.html.twig', [
            'conferences' => ($this->findAllConferences)(),
        ]);
    }

    #[Route('/conference/{slug:conference}', name: 'conference')]
    public function show(
        EntityManagerInterface $entityManager,
        Request $request,
        Conference $conference,
        #[MapQueryParameter]
        int $offset = 0
    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $paginator = ($this->getCommentPaginator)($conference, $offset);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug(),
            ]);
        }

        return ($this->render)('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::COMMENTS_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::COMMENTS_PER_PAGE),
            'comment_form' => $form,
        ]);
    }
}
