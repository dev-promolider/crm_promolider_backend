<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\SubMenu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu1 = new Menu();
        $menu1->name = 'Dashboard';
        $menu1->badge = '2';
        $menu1->badgeClass = 'badge badge-light-warning badge-pill ml-auto mr-1';
        $menu1->icon = 'home';
        $menu1->slug = 'menu-dashboard';
        $menu1->save();
        $submenu1 = new SubMenu();
        $submenu1->menu_id = $menu1->id;
        $submenu1->url = '/';
        $submenu1->name = 'eCommerce';
        $submenu1->icon = 'circle';
        $submenu1->slug = 'dashboard-ecommerce';
        $submenu1->save();
        /*--------------------------------*/
        // $menu2 = new Menu();
        // $menu2->name = 'User Membreship';
        // $menu2->dropdown = '1';
        // $menu2->icon = 'package';
        // $menu2->slug = 'menu-membreship';
        // $menu2->save();
        // $submenu2 = new SubMenu();
        // $submenu2->menu_id = $menu2->id;
        // $submenu2->url = 'users/register';
        // $submenu2->name = 'Register';
        // $submenu2->icon = 'mail';
        // $submenu2->slug = 'users-register';
        // $submenu2->save();
        /*--------------------------------*/
        $menu3 = new Menu();
        $menu3->name = 'Virtual Class Room';
        $menu3->dropdown = '1';
        $menu3->icon = 'package';
        $menu3->slug = 'menu-virtual-class';
        $menu3->save();
        $submenu3 = new SubMenu();
        $submenu3->menu_id = $menu3->id;
        $submenu3->url = 'creator/courses/create';
        $submenu3->name = 'Create course';
        $submenu3->icon = 'circle';
        $submenu3->slug = 'courses.create';
        $submenu3->save();
        $submenu4 = new SubMenu();
        $submenu4->menu_id = $menu3->id;
        $submenu4->url = 'creator/courses';
        $submenu4->name = 'List Courses';
        $submenu4->icon = 'circle';
        $submenu4->slug = 'courses.index';
        $submenu4->save();
        /*--------------------------------*/
        $menuAdmin = new Menu();
        $menuAdmin->navheader = 'Admin';
        $menuAdmin->slug = 'navheader-admin';
        $menuAdmin->save();
        $menuAdmin1 = new Menu();
        $menuAdmin1->url = 'admin/role';
        $menuAdmin1->name = 'Roles';
        $menuAdmin1->icon = 'circle';
        $menuAdmin1->slug = 'role.index';
        $menuAdmin1->save();
        /*--------------------------------*/
        $menu4 = new Menu();
        $menu4->navheader = 'Requests';
        $menu4->slug = 'navheader-request';
        $menu4->save();
        /*--------------------------------*/
        $menu5 = new Menu();
        $menu5->url = 'requests/listMyPayments';
        $menu5->name = 'List Payments';
        $menu5->icon = 'circle';
        $menu5->slug = 'request-listMyPayments';
        $menu5->save();
        /*--------------------------------*/
        $menu6 = new Menu();
        $menu6->navheader = 'Reports';
        $menu6->slug = 'navheader-report';
        $menu6->save();
        /*--------------------------------*/
        $menu7 = new Menu();
        $menu7->url = 'reports/wallets';
        $menu7->name = 'User Funds';
        $menu7->icon = 'circle';
        $menu7->slug = 'report-wallets';
        $menu7->save();
        /*--------------------------------*/
        $menuX = new Menu();
        $menuX->name = 'Marketing';
        $menuX->url = 'marketing';
        $menuX->icon = 'settings';
        $menuX->slug = 'marketing';
        $menuX->save();       
        /*--------------------------------*/
        $menu8 = new Menu();
        $menu8->url = 'reports/mywallet';
        $menu8->name = 'My Wallet';
        $menu8->icon = 'circle';
        $menu8->slug = 'report-nmywallet';
        $menu8->save();
        /*--------------------------------*/
        $menu9 = new Menu();
        $menu9->url = 'reports/startingBonus';
        $menu9->name = 'Starting Bonus List';
        $menu9->icon = 'circle';
        $menu9->slug = 'report-startingBonus';
        $menu9->save();
        /*--------------------------------*/
        $menu10 = new Menu();
        $menu10->url = 'reports/growthBonus';
        $menu10->name = 'Growth Bonus List';
        $menu10->icon = 'circle';
        $menu10->slug = 'report-growthBonus';
        $menu10->save();
        /*--------------------------------*/
        $menu11 = new Menu();
        $menu11->navheader = 'Config';
        $menu11->slug = 'navheader-config';
        $menu11->save();
        /*--------------------------------*/
        $menu = new Menu();
        $menu->url = 'admin/role';
        $menu->name = 'Roles';
        $menu->icon = 'circle';
        $menu->slug = 'role';
        $menu->save();
        $menu12 = new Menu();
        $menu12->url = 'config/user-request';
        $menu12->name = 'User Request';
        $menu12->icon = 'circle';
        $menu12->slug = 'user-request';
        $menu12->save();
        /*--------------------------------*/
        $menu13 = new Menu();
        $menu13->url = 'config/binarycut';
        $menu13->name = 'Binary Cut';
        $menu13->icon = 'circle';
        $menu13->slug = 'binarycut.index';
        $menu13->save();
        /*--------------------------------*/
        $menu14 = new Menu();
        $menu14->url = 'config/advertisements';
        $menu14->name = 'Advertisements';
        $menu14->icon = 'circle';
        $menu14->slug = 'advertisements';
        $menu14->save();
        /*--------------------------------*/
        $menu25 = new Menu();
        $menu25->url = 'config/share-link';
        $menu25->name = 'Share Link';
        $menu25->icon = 'circle';
        $menu25->slug = 'config.share-link';
        $menu25->save();
        /*--------------------------------*/
        $menu15 = new Menu();
        $menu15->url = 'config/bank';
        $menu15->name = 'Bank';
        $menu15->icon = 'circle';
        $menu15->slug = 'bank';
        $menu15->save();
        /*--------------------------------*/
        $menu16 = new Menu();
        $menu16->url = 'config/payment-method';
        $menu16->name = 'Payment Method';
        $menu16->icon = 'circle';
        $menu16->slug = 'payment-method';
        $menu16->save();
         /*--------------------------------*/
         $menu26 = new Menu();
         $menu26->url = 'config/classroom-point-config/list';
         $menu26->name = 'Classroom Point Config';
         $menu26->icon = 'circle';
         $menu26->slug = 'classroom-point-config';
         $menu26->save();
        /*--------------------------------*/
        $menu17 = new Menu();
        $menu17->url = 'account-type';
        $menu17->name = 'Account Type';
        $menu17->icon = 'circle';
        $menu17->slug = 'account-type';
        $menu17->save();
        /*--------------------------------*/
        $menu18 = new Menu();
        $menu18->url = 'starting-bonus';
        $menu18->name = 'Starting Bonus';
        $menu18->icon = 'circle';
        $menu18->slug = 'starting-bonus';
        $menu18->save();
        /*--------------------------------*/
        $menu19 = new Menu();
        $menu19->url = 'growth-bonus';
        $menu19->name = 'Growth Bonus';
        $menu19->icon = 'circle';
        $menu19->slug = 'growth-bonus';
        $menu19->save();
        /*--------------------------------*/
        $menu20 = new Menu();
        $menu20->navheader = 'Users';
        $menu20->slug = 'navheader-users';
        $menu20->save();
        /*--------------------------------*/
        $menu21 = new Menu();
        $menu21->url = 'system/binary-branch';
        $menu21->name = 'Red-Binary';
        $menu21->icon = 'briefcase';
        $menu21->slug = 'binary-branch';
        $menu21->save();
        /*--------------------------------*/
        $menu22 = new Menu();
        $menu22->name = 'User Scroll';
        $menu22->icon = 'users';
        $menu22->dropdown = '1';
        $menu22->slug = 'menu-user-scroll';
        $menu22->save();
        $submenu23 = new SubMenu();
        $submenu23->menu_id = $menu22->id;
        $submenu23->url = 'users/list';
        $submenu23->name = 'User List';
        $submenu23->icon = 'circle';
        $submenu23->slug = 'users-list';
        $submenu23->save();
        /*--------------------------------*/
        $menu23 = new Menu();
        $menu23->navheader = 'Payments';
        $menu23->slug = 'navheader-payments';
        $menu23->save();
        /*--------------------------------*/
        $menu24 = new Menu();
        $menu24->url = 'payment';
        $menu24->name = 'Payment';
        $menu24->icon = 'circle';
        $menu24->slug = 'payment';
        $menu24->save();
    }
}
