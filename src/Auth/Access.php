<?php

namespace Plasticode\Auth;

use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\Rights;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Models\User;
use Webmozart\Assert\Assert;

class Access
{
    private CacheInterface $cache;

    /**
     * Flattened actions
     */
    private array $actions;

    /**
     * Templates settings
     */
    private array $templates;

    /**
     * Rights settings
     */
    private array $rights;
    
    public function __construct(
        CacheInterface $cache,
        array $accessSettings
    )
    {
        $this->cache = $cache;

        $this->actions = $this->flattenActions($accessSettings['actions']);
        $this->templates = $accessSettings['templates'];
        $this->rights = $accessSettings['rights'];
    }
    
    /**
     * Flattens action tree.
     */
    private function flattenActions(
        array $tree,
        array $path = [],
        array $flat = []
    ) : array
    {
        $add = function ($node) use ($path, &$flat) {
            $path[] = $node;
            $flat[$node] = $path;
            
            return $path;
        };
        
        foreach ($tree as $node) {
            if (is_array($node)) {
                foreach ($node as $nodeTitle => $nodeTree) {
                    $pathCopy = $add($nodeTitle);
        
                    $flat = $this->flattenActions($nodeTree, $pathCopy, $flat);
                }
            } else {
                $add($node);
            }
        }
        
        return $flat;
    }

    /**
     * Check entity rights for action (also inherited)
     * for current user and role.
     */
    public function checkRights(
        string $entity,
        string $action,
        ?User $user
    ) : bool
    {
        $actionData = $this->actions[$action] ?? null;

        Assert::notNull(
            $actionData,
            'Unknown action: ' . $action
        );
        
        $grantAccess = false;

        $role = $user->role();

        if (is_null($role)) {
            return false;
        }

        $roleTag = $role->tag;
        
        $rights = $this->rights[$entity] ?? null;

        if (is_null($rights)) {
            throw new InvalidConfigurationException(
                'Access rights for entity "' . $entity . '" are not configured.'
            );
        }

        foreach ($actionData as $actionBit) {
            $grantAccess = $this->checkRightsForExactAction(
                $rights, $actionBit, $roleTag
            );
            
            if ($grantAccess) {
                break;
            }
        }

        return $grantAccess;
    }
    
    /**
     * Checks entity rights for exact action based on roleTag.
     */
    private function checkRightsForExactAction(
        array $rights,
        string $action,
        string $roleTag
    ) : bool
    {
        $grantAccess = false;

        if (isset($rights['template'])) {
            $tname = $rights['template'];

            if (!isset($this->templates[$tname])) {
                throw new InvalidConfigurationException(
                    'Unknown access rights template: ' . $tname
                );
            }
            
            $template = $this->templates[$tname];
            
            $grantAccess = in_array($action, $template[$roleTag] ?? []);
        }
        
        if (!$grantAccess) {
            $grantAccess = in_array($action, $rights[$roleTag] ?? []);
        }

        return $grantAccess;
    }
    
    /**
     * Get all entity rights for user.
     */
    public function getEntityRights(
        string $entity,
        ?User $user
    ) : Rights
    {
        $path = 'access.' . $entity;

        return $this->cache->getCached(
            $path,
            function () use ($entity, $user) {
                $can = [];
                $rights = array_keys($this->actions);
                
                foreach ($rights as $r) {
                    $can[$r] = $this->checkRights($entity, $r, $user);
                }

                return new Rights($user, $can);
            }
        );
    }
}
