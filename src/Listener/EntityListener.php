<?php

namespace App\Listener;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class EntityListener implements EventSubscriber
{
    private ?User $currentUser;

    public function __construct(Security $security)
    {
        // Use the Security service to get the currently logged-in user
        $this->currentUser = $security->getUser();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Check if the entity uses the trait
        if (method_exists($entity, 'setCreatedBy') && method_exists($entity, 'setCreatedOn')) {
            if ($this->currentUser) {
                $entity->setCreatedBy($this->currentUser);
            }
            $entity->setCreatedOn(new \DateTime());
            $entity->setUpdatedOn(new \DateTime());
            $entity->setDeleted(false); // Default value
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Check if the entity uses the trait
        if (method_exists($entity, 'setUpdatedOn')) {
            $entity->setUpdatedOn(new \DateTime());
        }
    }
}