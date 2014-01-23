<?php

namespace SymEdit\Bundle\MediaBundle\Doctrine\Orm;

use SymEdit\Bundle\MediaBundle\Doctrine\AbstractMediaListener;
use SymEdit\Bundle\MediaBundle\Model\MediaInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManager;

class MediaListener extends AbstractMediaListener
{
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,

            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,

            Events::onFlush,
            Events::postLoad,
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        if ($this->isValid($object)) {
            $this->preUpload($object);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        if ($this->isValid($object)) {
            $this->preUpload($object);

            /**
             * Compute changeset
             */
            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($object));
            $uow->recomputeSingleEntityChangeSet($meta, $object);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        if ($this->isValid($object)) {
            $this->upload($object);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        if ($this->isValid($object)) {
            $this->upload($object);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        if ($this->isValid($object)) {
            $this->removeUpload($object);
        }
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $object) {
            if (!$this->isValid($object)) {
                continue;
            }

            $meta = $em->getClassMetadata(get_class($object));

            if ($callback = $object->getNameCallback()) {
                $object->setName($callback($object));
                $object->setPath($object->getUploadName());
                $uow->recomputeSingleEntityChangeSet($meta, $object);
            }

            if ($this->makePathUnique($object, $em, $meta)) {
                $object->setPath($object->getUploadName());
                $uow->recomputeSingleEntityChangeSet($meta, $object);
            }
        }
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $object = $eventArgs->getEntity();

        if ($this->isValid($object)) {
            $this->setPrefix($object);
        }
    }

    protected function makePathUnique(MediaInterface $media, EntityManager $em)
    {
        $class = get_class($media);
        $name = $media->getName();
        $entity = $em->getRepository($class)->findOneBy(array('name' => $name));

        /**
         * No entity found, must be unique
         */
        if (!$entity) {
            return false;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('m.name')
           ->from($class, 'm')
           ->where($qb->expr()->like(
               'm.name', $qb->expr()->literal($name . '%')
           ))
           ->orderBy('m.name');

        $query = $qb->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $result = $query->execute();

        $sameNames = array();
        foreach ($result as $record) {
            $sameNames[] = strtolower($record['name']);
        }

        $i = 1;
        do {
            $newName = $name . '-' . $i++;
        } while (in_array(strtolower($newName), $sameNames));

        $media->setName($newName);

        return true;
    }
}