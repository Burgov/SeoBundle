<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SeoBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Cmf\Bundle\SeoBundle\DependencyInjection\Compiler\RegisterExtractorsPass;
use Symfony\Cmf\Bundle\SeoBundle\DependencyInjection\Compiler\UnescapePlaceholdersPass;

class CmfSeoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterExtractorsPass());
        $container->addCompilerPass(new UnescapePlaceholdersPass(array(
            'cmf_seo.title',
            'cmf_seo.description',
        )), PassConfig::TYPE_OPTIMIZE);

        $this->buildPhpcrCompilerPass($container);
        $this->buildOrmCompilerPass($container);
    }


    /**
     * Creates and registers compiler passes for PHPCR-ODM mapping if both the
     * phpcr-odm and the phpcr-bundle are present.
     *
     * @param ContainerBuilder $container
     */
    private function buildPhpcrCompilerPass(ContainerBuilder $container)
    {
        if (!class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')
            || !class_exists('Doctrine\ODM\PHPCR\Version')
        ) {
            return;
        }

        $container->addCompilerPass(
            DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__ . '/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\SeoBundle\Model',
                    realpath(__DIR__ . '/Resources/config/doctrine-phpcr') => 'Symfony\Cmf\Bundle\SeoBundle\Doctrine\Phpcr'
                ),
                array('cmf_seo.dynamic.persistence.phpcr.manager_name'),
                'cmf_seo.backend_type_phpcr'
            )
        );
    }

    /**
     * Creates and registers compiler passes for ORM mappings if both doctrine
     * ORM and a suitable compiler pass implementation are available.
     *
     * @param ContainerBuilder $container
     */
    private function buildOrmCompilerPass(ContainerBuilder $container)
    {
        if (!class_exists('Doctrine\ORM\Version')
            || !class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass')
            || !class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')
        ) {
            return;
        }

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__ . '/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\SeoBundle\Model',
                ),
                array('cmf_routing.dynamic.persistence.orm.manager_name'),
                'cmf_routing.backend_type_orm'
            )
        );
    }
}
