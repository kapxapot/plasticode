<?php

namespace Plasticode\Auth;

use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Models\User;
use Webmozart\Assert\Assert;

class Access
{
    /**
     * Authentication context
     *
     * @var Auth
     */
    private $auth;

    /** @var CacheInterface */
    private $cache;

    /**
     * Flattened actions
     *
     * @var array
     */
    private $actions;

    /**
     * Templates settings
     *
     * @var array
     */
    private $templates;

    /**
     * Rights settings
     *
     * @var array
     */
    private $rights;
    
    public function __construct(
        Auth $auth,
        CacheInterface $cache,
        array $accessSettings
    )
    {
        $this->auth = $auth;
        $this->cache = $cache;

        $this->actions = $this->flattenActions($accessSettings['actions']);
        $this->templates = $accessSettings['templates'];
        $this->rights = $accessSettings['rights'];
    }

    public function getUser() : ?User
    {
        return $this->auth->getUser();
    }
    
    /**
     * Flattens action tree.
     *
     * @param array $tree
     * @param array $path
     * @param array $flat
     * @return array
     */
    private function flattenActions(array $tree, array $path = [], array $flat = []) : array
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
     *
     * @param string $entity
     * @param string $action
     * @return boolean
     */
    public function checkRights(string $entity, string $action) : bool
    {
        $actionData = $this->actions[$action] ?? null;

        Assert::notNull(
            $actionData,
            'Unknown action: ' . $action
        );
        
        $grantAccess = false;
        
        $role = $this->auth->getRole();

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
     *
     * @param array $rights
     * @param string $action
     * @param string $roleTag
     * @return boolean
     */
    private function checkRightsForExactAction(array $rights, string $action, string $roleTag) : bool
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
     * Get all rights for the entity for current user and role.
     *
     * @param string $entity
     * @return array
     */
    public function getAllRights(string $entity) : array
    {
        $path = 'access.' . $entity;

        return $this->cache->getCached(
            $path,
            function () use ($entity) {
                $can = [];
                $rights = array_keys($this->actions);
                
                foreach ($rights as $r) {
                    $can[$r] = $this->checkRights($entity, $r);
                }

                return $can;
            }
        );
    }
}
