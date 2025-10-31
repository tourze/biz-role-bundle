<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\BizRoleBundle\Entity\BizRole;

/**
 * @extends AbstractCrudController<BizRole>
 */
#[AdminCrud(routePath: '/biz/role', routeName: 'biz_role')]
final class BizRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BizRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('角色')
            ->setEntityLabelInPlural('角色管理')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'title'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, '角色管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建角色')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑角色')
            ->setPageTitle(Crud::PAGE_DETAIL, '角色详情')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        yield TextField::new('name', '角色名称')
            ->setRequired(true)
            ->setHelp('唯一的角色标识符，如：ROLE_ADMIN')
            ->setColumns(6)
        ;

        yield TextField::new('title', '角色标题')
            ->setRequired(true)
            ->setHelp('角色的显示名称')
            ->setColumns(6)
        ;

        yield BooleanField::new('admin', '系统管理员')
            ->setHelp('是否为系统管理员角色')
            ->renderAsSwitch(false)
        ;

        yield BooleanField::new('valid', '有效状态')
            ->setHelp('是否启用此角色')
            ->renderAsSwitch(false)
        ;

        if (Crud::PAGE_DETAIL === $pageName || Crud::PAGE_INDEX === $pageName) {
            yield IntegerField::new('users.count', '用户数量')
                ->formatValue(function ($value, BizRole $entity) {
                    return $entity->getUsers()->count();
                })
            ;
        }

        yield ArrayField::new('permissions', '权限列表')
            ->hideOnIndex()
            ->setHelp('角色拥有的权限列表')
        ;

        yield ArrayField::new('hierarchicalRoles', '继承角色')
            ->hideOnIndex()
            ->setHelp('此角色继承的其他角色权限')
        ;

        yield ArrayField::new('excludePermissions', '排除权限')
            ->hideOnIndex()
            ->setHelp('要排除的权限列表')
        ;

        yield TextareaField::new('menuJson', '自定义菜单')
            ->hideOnIndex()
            ->setHelp('自定义菜单配置JSON')
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('createdBy', '创建人')->hideOnForm();
            yield TextField::new('updatedBy', '更新人')->hideOnForm();
            yield TextField::new('createdFromIp', '创建IP')->hideOnForm();
            yield TextField::new('updatedFromIp', '更新IP')->hideOnForm();
            yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
            yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '角色名称'))
            ->add(TextFilter::new('title', '角色标题'))
            ->add(BooleanFilter::new('admin', '系统管理员'))
            ->add(BooleanFilter::new('valid', '有效状态'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizRole);
        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('角色"%s"创建成功！', $entityInstance->getTitle()));
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizRole);
        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('角色"%s"更新成功！', $entityInstance->getTitle()));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof BizRole);
        $title = $entityInstance->getTitle();

        if ($entityInstance->getUsers()->count() > 0) {
            $this->addFlash('danger', sprintf('角色"%s"下还有用户，无法删除！', $title));

            return;
        }

        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', sprintf('角色"%s"删除成功！', $title));
    }
}
