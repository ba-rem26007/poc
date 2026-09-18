<?php

namespace App\Controller\Admin;

use App\Entity\ClientLog;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('3-Tier TLS SuperAdmin Dashboard');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Products', 'fas fa-box', Product::class);
        yield MenuItem::linkToCrud('Commandes', 'fas fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Logs Client', 'fas fa-bug', ClientLog::class);
        yield MenuItem::linkToUrl('Documentation & ADRs', 'fas fa-sitemap', '/doc');
        yield MenuItem::linkToUrl('API Docs (Swagger)', 'fas fa-code', '/api');
        yield MenuItem::linkToRoute('Redéployer App (Webhook)', 'fas fa-sync-alt', 'admin_trigger_redeploy');
        yield MenuItem::linkToLogout('Logout', 'fas fa-sign-out-alt');
    }
}
