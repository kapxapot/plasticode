<?php

namespace Plasticode\Auth;

use Plasticode\Data\Rights;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Models\User;
use Webmozart\Assert\Assert;

class Access
{
    /**
     * All known actions data (flattened)
     * 
     * key = action name
     * value = array of parent actions from root (path)
     * 
     * @var array<string, string[]>
     */
    private array $actionData;

    /**
     * Rights templates settings
     * 
     * @var array<string, array<string, string[]>>
     */
    private array $rightsTemplates;

    /**
     * Table rights settings
     * 
     * @var array<string, array<string, string|string[]>>
     */
    private array $rightsSettings;

    public function __construct(
        array $accessSettings = []
    )
    {
        $this->actionData = $this->flattenActionData($accessSettings['actions'] ?? []);
        $this->rightsTemplates = $accessSettings['templates'] ?? [];
        $this->rightsSettings = $accessSettings['rights'] ?? [];
    }

    /**
     * Flattens action tree.
     */
    private function flattenActionData(
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

                    $flat = $this->flattenActionData($nodeTree, $pathCopy, $flat);
                }
            } else {
                $add($node);
            }
        }

        return $flat;
    }

    /**
     * Returns all table rights for user.
     * 
     * @param string $table Plural entity name by default like 'articles'
     */
    public function getTableRights(string $table, ?User $user) : Rights
    {
        /** @var array<string, boolean> */
        $can = [];

        $actions = $this->actionNames();

        foreach ($actions as $action) {
            $can[$action] = $this->checkActionRights($table, $action, $user);
        }

        return new Rights($user, $can);
    }

    /**
     * Checks table rights for action (also inherited)
     * for current user and role.
     */
    public function checkActionRights(
        string $table,
        string $action,
        ?User $user
    ) : bool
    {
        $role = $user ? $user->role() : null;

        if (is_null($role)) {
            return false;
        }

        $actionPath = $this->actionPath($action);

        foreach ($actionPath as $ancestorAction) {
            $grant = $this->checkRightsForExactAction(
                $table,
                $ancestorAction,
                $role->tag
            );

            if ($grant) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks entity rights for exact action based on roleTag.
     * 
     * @var array<string, string|string[]> $tableRightsSettings
     */
    private function checkRightsForExactAction(
        string $table,
        string $action,
        string $roleTag
    ) : bool
    {
        $grant = false;

        $tableRights = $this->tableRights($table);

        $tName = $tableRights['template'] ?? null;

        if ($tName) {
            $template = $this->rightsTemplate($tName);

            $grant = $this->checkRoleRights(
                $template[$roleTag] ?? [],
                $action
            );
        }

        return $grant
            || $this->checkRoleRights(
                $tableRights[$roleTag] ?? [],
                $action
            );
    }

    /**
     * @param string[] $roleRights
     */
    private function checkRoleRights(array $roleRights, string $action) : bool
    {
        return in_array($action, $roleRights);
    }

    /**
     * Returns the list of known action names.
     *
     * @return string[]
     */
    private function actionNames() : array
    {
        return array_keys($this->actionData);
    }

    /**
     * Returns action path.
     *
     * @return string[]
     */
    private function actionPath(string $action) : array
    {
        $actionPath = $this->actionData[$action] ?? null;

        Assert::notNull(
            $actionPath,
            'Unknown action: ' . $action
        );

        return $actionPath;
    }

    /**
     * Returns rights template by name.
     *
     * @return array<string, string[]>
     */
    private function rightsTemplate(string $name) : array
    {
        $template = $this->rightsTemplates[$name] ?? null;

        if (is_null($template)) {
            throw new InvalidConfigurationException(
                'Undefined access rights template: ' . $name
            );
        }

        return $template;
    }

    /**
     * Returns table rights settings.
     * 
     * @return array<string, string|string[]>
     */
    private function tableRights(string $table) : array
    {
        $tableRights = $this->rightsSettings[$table] ?? null;

        if (is_null($tableRights)) {
            throw new InvalidConfigurationException(
                'Access rights for table "' . $table . '" are not configured.'
            );
        }

        return $tableRights;
    }
}
