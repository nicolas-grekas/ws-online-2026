<?php

namespace App\Twig;

use App\Repository\ConferenceRepository;
use App\Service\FooInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Attribute\AsTwigFunction;

class AppExtension
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
    )
    {
    }

    #[AsTwigFunction('conferences')]
    public function getConferences(): array
    {
        return $this->conferenceRepository->findAll();
    }
}
