<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Staff\Acl;

class Builder
{
    /**
     * Acl object
     *
     * @var \Magento\Framework\Acl
     */
    protected $_acl;

    /**
     * Acl loader list
     *
     * @var \Magento\Framework\Acl\LoaderInterface[]
     */
    protected $_loaderPool;

    /**
     * @var \Magento\Framework\AclFactory
     */
    protected $_aclFactory;

    /**
     * @param \Magento\Framework\AclFactory $aclFactory
     * @param \Magento\Framework\Acl\LoaderInterface $roleLoader
     * @param \Magento\Framework\Acl\LoaderInterface $resourceLoader
     * @param \Magento\Framework\Acl\LoaderInterface $ruleLoader
     */
    public function __construct(
        \Magento\Framework\AclFactory $aclFactory,
        \Magento\Framework\Acl\LoaderInterface $roleLoader,
        \Magento\Framework\Acl\LoaderInterface $resourceLoader,
        \Magento\Framework\Acl\LoaderInterface $ruleLoader
    ) {
        $this->_aclFactory = $aclFactory;
        $this->_loaderPool = [$roleLoader, $resourceLoader, $ruleLoader];
    }

    /**
     * Build Access Control List
     *
     * @return \Magento\Framework\Acl
     * @throws \LogicException
     */
    public function getAcl()
    {
        if ($this->_acl instanceof \Magento\Framework\Acl) {
            return $this->_acl;
        }

        try {
            $this->_acl = $this->_aclFactory->create();
            foreach ($this->_loaderPool as $loader) {
                $loader->populateAcl($this->_acl);
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not create an acl object: ' . $e->getMessage());
        }

        return $this->_acl;
    }

    /**
     * Remove cached ACL instance.
     *
     * @return $this
     * @since 100.2.0
     */
    public function resetRuntimeAcl()
    {
        $this->_acl = null;
        return $this;
    }
}
