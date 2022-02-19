<?php

declare(strict_types=1);

namespace Shapecode\Bundle\CronBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Shapecode\Bundle\CronBundle\Annotation\CronJob as CronJobAnnotation;
use Shapecode\Bundle\CronBundle\Attribute\CronJob as CronJobAttribute;
use Shapecode\Bundle\CronBundle\Event\LoadJobsEvent;
use Shapecode\Bundle\CronBundle\Model\CronJobMetadata;
use Shapecode\Bundle\CronBundle\Service\AttributeReader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;
use function is_string;
use function array_merge;

final class AnnotationJobLoaderListener implements EventSubscriberInterface
{
    private Application $application;

    public function __construct(
        KernelInterface $kernel,
        private readonly Reader $reader,
        private readonly AttributeReader $attributeReader,
    ) {
        $this->application = new Application($kernel);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [LoadJobsEvent::NAME => 'onLoadJobs'];
    }

    public function onLoadJobs(LoadJobsEvent $event): void
    {
        foreach ($this->application->all() as $command) {
            if ($command instanceof LazyCommand) {
                $command = $command->getCommand();
            }

            // Check for an @CronJob annotation
            $reflectionClass = new ReflectionClass($command);

            $annotations = array_filter(
                $this->reader->getClassAnnotations($reflectionClass),
                fn ($annotation) => $annotation instanceof CronJobAnnotation,
            );

            $annotations = array_merge(
                $annotations,
                $this->attributeReader->getClassAttributes($reflectionClass, CronJobAttribute::class),
            );

            foreach ($annotations as $annotation) {
                $arguments    = $annotation->arguments;
                $maxInstances = $annotation->maxInstances;
                $schedule     = $annotation->value;
                assert(is_string($schedule));

                $meta = CronJobMetadata::createByCommand($schedule, $command, $arguments, $maxInstances);
                $event->addJob($meta);
            }
        }
    }
}
