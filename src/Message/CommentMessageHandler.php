<?php

namespace App\Message;

use App\Entity\Comment;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CommentMessageHandler
{
    public function __construct(
        private readonly SpamChecker $spamChecker,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    public function __invoke(CommentMessage $message): void
    {
        $comment = $this->entityManager->getRepository(Comment::class)->find($message->id);

        if (!$comment) {
            return;
        }

        if (2 === $this->spamChecker->getSpamScore($comment, $message->context)) {
            $comment->setState('spam');
        } else {
            $comment->setState('published');
        }

        $this->entityManager->flush();

    }
}