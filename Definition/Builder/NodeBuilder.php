<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Definition\Builder;

/**
 * This class provides a fluent interface for building a config tree.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class NodeBuilder
{
    /************
     * READ-ONLY
     ************/
    public $name;
    public $type;
    public $key;
    public $parent;
    public $children;
    public $prototype;
    public $normalization;
    public $validation;
    public $merge;
    public $finalization;
    public $defaultValue;
    public $default;
    public $addDefaults;
    public $required;
    public $atLeastOne;
    public $allowNewKeys;
    public $allowEmptyValue;
    public $nullEquivalent;
    public $trueEquivalent;
    public $falseEquivalent;
    public $performDeepMerging;
    public $allowUnnamedChildren;

    /**
     * Constructor
     *
     * @param string      $name   the name of the node
     * @param string      $type   The type of the node
     * @param NodeBuilder $parent
     */
    public function __construct($name, $type, $parent = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->parent = $parent;

        $this->default = false;
        $this->required = false;
        $this->addDefaults = false;
        $this->allowNewKeys = true;
        $this->atLeastOne = false;
        $this->allowEmptyValue = true;
        $this->children = array();
        $this->performDeepMerging = true;
        $this->allowUnnamedChildren = false;

        if ('boolean' === $type) {
            $this->nullEquivalent = true;
        } else if ('array' === $type) {
            $this->nullEquivalent = array();
        }

        if ('array' === $type) {
            $this->trueEquivalent = array();
        } else {
            $this->trueEquivalent = true;
        }

        $this->falseEquivalent = false;
    }

    /****************************
     * FLUID INTERFACE
     ****************************/

    /**
     * Creates a child node.
     *
     * @param string $name The name of the node
     * @param string $type The type of the node
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder The builder of the child node
     */
    public function node($name, $type)
    {
        $node = new static($name, $type, $this);

        return $this->children[$name] = $node;
    }

    /**
     * Add a NodeBuilder instance directly.
     *
     * This helps achieve a fluid interface when a method on your Configuration
     * class returns a pre-build NodeBuilder instance on your behalf:
     *
     *     $root = new NodeBuilder();
     *         ->node('foo', 'scalar')
     *         ->addNodeBuilder($this->getBarNodeBuilder())
     *         ->node('baz', 'scalar')
     *     ;
     *
     * @return Symfony\Component\DependencyInjection\Configuration\Builder\NodeBuilder This builder node
     */
    public function addNodeBuilder(NodeBuilder $node)
    {
        $node->parent = $this;

        $this->children[$node->name] = $node;

        return $this;
    }

    /**
     * Creates a child array node
     *
     * @param string $name The name of the node
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder The builder of the child node
     */
    public function arrayNode($name)
    {
        return $this->node($name, 'array');
    }

    /**
     * Creates a child scalar node.
     *
     * @param string $name the name of the node
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder The builder of the child node
     */
    public function scalarNode($name)
    {
        return $this->node($name, 'scalar');
    }

    /**
     * Creates a child boolean node.
     *
     * @param string $name The name of the node
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder The builder of the child node
     */
    public function booleanNode($name)
    {
        return $this->node($name, 'boolean');
    }

    /**
     * Sets the default value.
     *
     * @param mixed $value The default value
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function defaultValue($value)
    {
        $this->default = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Sets the node as required.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function isRequired()
    {
        $this->required = true;

        return $this;
    }

    /**
     * Requires the node to have at least one element.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function requiresAtLeastOneElement()
    {
        $this->atLeastOne = true;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains null.
     *
     * @param mixed $value
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function treatNullLike($value)
    {
        $this->nullEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains true.
     *
     * @param mixed $value
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function treatTrueLike($value)
    {
        $this->trueEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains false.
     *
     * @param mixed $value
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function treatFalseLike($value)
    {
        $this->falseEquivalent = $value;

        return $this;
    }

    /**
     * Sets null as the default value.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function defaultNull()
    {
        return $this->defaultValue(null);
    }

    /**
     * Sets true as the default value.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function defaultTrue()
    {
        return $this->defaultValue(true);
    }

    /**
     * Sets false as the default value.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function defaultFalse()
    {
        return $this->defaultValue(false);
    }

    /**
     * Adds the default value if the node is not set in the configuration.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function addDefaultsIfNotSet()
    {
        $this->addDefaults = true;

        return $this;
    }

    /**
     * Disallows adding news keys in a subsequent configuration.
     *
     * If used all keys have to be defined in the same configuration file.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function disallowNewKeysInSubsequentConfigs()
    {
        $this->allowNewKeys = false;

        return $this;
    }

    /**
     * Gets the builder for normalization rules.
     *
     * @return Symfony\Component\Config\Definition\Builder\NormalizationBuilder
     */
    protected function normalization()
    {
        if (null === $this->normalization) {
            $this->normalization = new NormalizationBuilder($this);
        }

        return $this->normalization;
    }

    /**
     * Sets an expression to run before the normalization.
     *
     * @return Symfony\Component\Config\Definition\Builder\ExprBuilder
     */
    public function beforeNormalization()
    {
        return $this->normalization()->before();
    }

    /**
     * Sets a normalization rule for XML configurations.
     *
     * @param string $singular The key to remap
     * @param string $plural   The plural of the key for irregular plurals
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function fixXmlConfig($singular, $plural = null)
    {
        $this->normalization()->remap($singular, $plural);

        return $this;
    }

    /**
     * Sets an attribute to use as key.
     *
     * @param string $name
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function useAttributeAsKey($name)
    {
        $this->key = $name;

        return $this;
    }

    /**
     * Gets the builder for merging rules.
     *
     * @return Symfony\Component\Config\Definition\Builder\MergeBuilder
     */
    protected function merge()
    {
        if (null === $this->merge) {
            $this->merge = new MergeBuilder($this);
        }

        return $this->merge;
    }

    /**
     * Sets whether the node can be overwritten.
     *
     * @param boolean $deny Whether the overwritting is forbidden or not
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function cannotBeOverwritten($deny = true)
    {
        $this->merge()->denyOverwrite($deny);

        return $this;
    }

    /**
     * Denies the node value being empty.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function cannotBeEmpty()
    {
        $this->allowEmptyValue = false;

        return $this;
    }

    /**
     * Sets whether the node can be unset.
     *
     * @param boolean $allow
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function canBeUnset($allow = true)
    {
        $this->merge()->allowUnset($allow);

        return $this;
    }

    /**
     * Sets a prototype for child nodes.
     *
     * @param string $type the type of node
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function prototype($type)
    {
        return $this->prototype = new static(null, $type, $this);
    }

    /**
     * Disables the deep merging of the node.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function performNoDeepMerging()
    {
        $this->performDeepMerging = false;

        return $this;
    }

    /**
     * Gets the builder for validation rules.
     *
     * @return Symfony\Component\Config\Definition\Builder\ValidationBuilder
     */
    protected function validation()
    {
        if (null === $this->validation) {
            $this->validation = new ValidationBuilder($this);
        }

        return $this->validation;
    }

    /**
     * Sets an expression to run for the validation.
     *
     * The expression receives the value of the node and must return it. It can
     * modify it.
     * An exception should be thrown when the node is not valid.
     *
     * @return Symfony\Component\Config\Definition\Builder\ExprBuilder
     */
    public function validate()
    {
        return $this->validation()->rule();
    }

    /**
     * Returns the parent node.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * Allows child values not represented by a node.
     *
     * An example would be an "options" array node, where its children
     * could be any key of any form. In this case, no children are placed
     * on the node, but child values must be allowed.
     *
     * @return Symfony\Component\DependencyInjection\Configuration\Builder\NodeBuilder
     */
    public function allowUnnamedChildren()
    {
        $this->allowUnnamedChildren = true;

        return $this;
    }
}