<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Chain;

use Scribe\CacheBundle\Cache\Handler\Type\AbstractHandlerType;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Class HandlerChain
 *
 * @package Scribe\CacheBundle\Cache\Handler\Chain
 */
class HandlerChain extends AbstractHandlerChain implements HandlerChainInterface
{
    /**
     * Sets the active handler
     *
     * @param  AbstractHandlerType $handler
     * @return $this
     */
    protected function setActiveHandler(AbstractHandlerType $handler)
    {
        $this->activeHandler = $handler;

        return $this;
    }

    /**
     * Stack the provided handler in the correct position on the handlers stack,
     * verifying that another handler does not already have the same priority.
     *
     * @param  AbstractHandlerType $handler
     * @return $this
     * @throws RuntimeException
     */
    protected function determineStackPosition(AbstractHandlerType $handler)
    {
        static $internalHandlerPriority = 100;

        if (null === ($handlerPriority = $handler->getPriority())) {
            $handlerPriority = $internalHandlerPriority++;
        }

        if (true === array_key_exists($handlerPriority, $this->handlers)) {
            throw new RuntimeException(sprintf(
               'A duplicate priority of %d cannot be set for %s. Please review your config.',
               $handlerPriority,
               $handler->getType()
           ));
        }

        $this->handlers[ $handlerPriority ] = $handler;

        return $this;
    }

    /**
     * Each time a new handler is added to the stack, re-determine the active
     * handler by processing them by priority (index value) and checking for the
     * first handler type that is both enabled and supported.
     *
     * @return $this
     */
    protected function determineActiveHandler()
    {
        foreach ($this->getHandlers() as $handler) {
            if (true === $handler->isEnabled() &&
                true === $handler->isSupported())
            {
                $this->setActiveHandler($handler);
                break;
            }
        }

        return $this;
    }
}

/* EOF */
