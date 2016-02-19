    <?php

//module::con::action
class Engine_Vault_Acl extends Zend_Acl {

    public function __construct() {
        // Add a new role called "guest"
        $this->addRole(new Zend_Acl_Role('guest'));
        // Add a role called user, which inherits from guest
        $this->addRole(new Zend_Acl_Role('user'), 'guest');
        // Add a role called admin, which inherits from user
        $this->addRole(new Zend_Acl_Role('admin'), 'user');

        $this->add(new Zend_Acl_Resource('home'))
                ->add(new Zend_Acl_Resource('home::home'), 'home')
                ->add(new Zend_Acl_Resource('home::error'), 'home')
                ->add(new Zend_Acl_Resource('home::error::error'), 'home::error')
                ->add(new Zend_Acl_Resource('home::home::home'), 'home::home')
                ->add(new Zend_Acl_Resource('home::home::contest-information'), 'home::home');

          $this->allow('guest', 'home::home::home')
              ->allow('guest', 'home::error::error');

        /*
         * Developer: Namrata Singh
         * Authentication Module
         */
        //module::con::action
        $this->add(new Zend_Acl_Resource('authentication'))
                ->add(new Zend_Acl_Resource('authentication::authentication'), 'authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::signup'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::ajaxHandler'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::logout'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::twittersignup'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::twitterauth'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::facebookauth'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::signin'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::step1'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::step2'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::auth-ajax-handler'), 'authentication::authentication')
                ->add(new Zend_Acl_Resource('authentication::authentication::lang'), 'authentication::authentication');


        $this->allow('guest', 'authentication::authentication::signup')
                ->allow('guest', 'authentication::authentication::ajaxHandler')
                ->allow('guest', 'authentication::authentication::logout')
                ->allow('guest', 'authentication::authentication::twittersignup')
                ->allow('guest', 'authentication::authentication::twitterauth')
                ->allow('guest', 'authentication::authentication::signin')
                ->allow('guest', 'authentication::authentication::facebookauth')
                ->allow('guest', 'authentication::authentication::step1')
                ->allow('guest', 'authentication::authentication::step2')
                ->allow('guest', 'authentication::authentication::auth-ajax-handler')
                ->allow('guest', 'authentication::authentication::lang');


        /**
         * Developer: Namrata Singh
         * Users Module
         */
        $this->add(new Zend_Acl_Resource('users'))
                ->add(new Zend_Acl_Resource('users::users'), 'users')
                ->add(new Zend_Acl_Resource('users::users::dashboard'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::user-profile'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::dashboardclasses'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::following'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::taking'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::discussion'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::profileDetails'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::cart'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::membership'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::classes-ajax-handler'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::classes'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::search'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::noresult'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::resetpassword'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::my-classes'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::teaching'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::teacher-dashboard'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::user-follow'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::class-enrolled'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::class-saved'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::class-teaching'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::notificationstatus'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::project-created'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::set-password'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::trending'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::highly-rated'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::recently-added'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::classestaking'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::delete-projects'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::leaderboard'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::removesavedclass'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::badges'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::categoryjshandler'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::fashionlearnclub'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::ifollowing'), 'users::users')       
                ->add(new Zend_Acl_Resource('users::users::myfollowers'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::student-conversion'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::follow-ajax-handler'), 'users::users')
                ->add(new Zend_Acl_Resource('home::home::projectsbeforelogin'), 'home::home')
                ->add(new Zend_Acl_Resource('users::users::finance-dashboard'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::fashionclub-ajax-handler'), 'users::users')
                 ->add(new Zend_Acl_Resource('users::users::invoice'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::cluborder'), 'users::users')
                ->add(new Zend_Acl_Resource('users::users::classfilter-by-month'), 'users::users');




//        $this->allow('guest', 'users::users')
            $this->allow('guest', 'users::users::classes')
                ->allow('guest', 'users::users::user-profile')
                ->allow('user', 'users::users::dashboardclasses')
                ->allow('user', 'users::users::following')
                ->allow('user', 'users::users::taking')
                ->allow('user', 'users::users::discussion')
                ->allow('user', 'users::users::profileDetails')
                ->allow('user', 'users::users::cart')
                ->allow('guest', 'users::users::classes-ajax-handler')
                ->allow('user', 'users::users::dashboard')
                ->allow('guest', 'users::users::search')
                ->allow('guest', 'users::users::noresult')
                ->allow('guest', 'users::users::resetpassword')
                ->allow('user', 'users::users::my-classes')
                ->allow('user', 'users::users::teaching')
                ->allow('user', 'users::users::teacher-dashboard')
                ->allow('user', 'users::users::user-follow')
                ->allow('user', 'users::users::class-enrolled')
                ->allow('user', 'users::users::class-saved')
                ->allow('user', 'users::users::class-teaching')
                ->allow('user', 'users::users::project-created')
                ->allow('user', 'users::users::notificationstatus')
                ->allow('guest', 'users::users::set-password')
                ->allow('guest', 'users::users::trending')
                ->allow('guest', 'users::users::highly-rated')
                ->allow('guest', 'users::users::recently-added')
                ->allow('guest', 'users::users::classestaking')    
                ->allow('user', 'users::users::delete-projects')
                ->allow('user', 'users::users::leaderboard')
                 ->allow('user', 'users::users::removesavedclass')
                ->allow('user', 'users::users::badges')
                ->allow('user', 'users::users::categoryjshandler')
                ->allow('user', 'users::users::fashionlearnclub')
                ->allow('user', 'users::users::ifollowing')
                ->allow('user', 'users::users::myfollowers')
                ->allow('user', 'users::users::student-conversion')
                ->allow('user', 'users::users::follow-ajax-handler')
                ->allow('guest', 'home::home::projectsbeforelogin')
                ->allow('user', 'users::users::finance-dashboard')    
                ->allow('user', 'users::users::fashionclub-ajax-handler')
                ->allow('user', 'users::users::cluborder')
                ->allow('user', 'users::users::invoice')
                     ->allow('user', 'users::users::classfilter-by-month')
                ;    




        /**
         * Developer: Namrata Singh
         * membership Module
         */
        $this->add(new Zend_Acl_Resource('membership'))
                ->add(new Zend_Acl_Resource('membership::membership'), 'membership')
                ->add(new Zend_Acl_Resource('membership::membership::membership'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::premiummembership'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::businesspage'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::ssbusiness'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::ssenterprise'), 'membership::membership')
                ->add(new Zend_Acl_Resource('membership::membership::subscription'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::premiummonthly'), 'membership::membership')
//                ->add(new Zend_Acl_Resource('membership::membership::one-time-payment'), 'membership::membership')
                ->add(new Zend_Acl_Resource('membership::membership::upgrade'), 'membership::membership')
                ->add(new Zend_Acl_Resource('membership::membership::membership-ajax-handler'), 'membership::membership')
                ->add(new Zend_Acl_Resource('membership::membership::cancel-ajax-handler'), 'membership::membership')
                ->add(new Zend_Acl_Resource('membership::membership::upgrade-membership-ajax-handler'), 'membership::membership');




        $this->allow('guest', 'membership::membership')
                ->allow('guest', 'membership::membership::membership')
//                ->allow('guest', 'membership::membership::premiummembership')
//                ->allow('guest', 'membership::membership::businesspage')
//                ->allow('guest', 'membership::membership::ssbusiness')
//                ->allow('guest', 'membership::membership::ssenterprise')
                ->allow('guest', 'membership::membership::subscription')
//                ->allow('guest', 'membership::membership::premiummonthly')
//                ->allow('guest', 'membership::membership::one-time-payment')
                ->allow('guest', 'membership::membership::upgrade')
                ->allow('guest', 'membership::membership::membership-ajax-handler')
                ->allow('guest', 'membership::membership::cancel-ajax-handler')
                ->allow('guest', 'membership::membership::upgrade-membership-ajax-handler');



        /**
         * Developer: Namrata Singh
         * Teach Module
         */
        $this->add(new Zend_Acl_Resource('teach'))
                ->add(new Zend_Acl_Resource('teach::teach'), 'teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teachdetail'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-ajax-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-file-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-video-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-doc-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-delete-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-class'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::class-discussion'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::view-discussion'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::view-comment'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::project-comment'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::create-projects'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::view-projects'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::show-project-form'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::show-project'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::project-likes'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::project-dislikes'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::project-by-category'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::image-upload'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::show-discussion'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::leave-review'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::display-discussion'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::videostatus'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::teach-cover-image'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::generate-certificate'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::certificate'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::loading-vimeourl'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::allclasses'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::allclassnext'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::projects'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::classteach'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::tooltip'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::project-image'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::create-project-handler'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::application'), 'teach::teach')
                ->add(new Zend_Acl_Resource('teach::teach::update-social-share'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::edit-project-handler'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::commentreply'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::project-scroll'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::recent-project-scroll'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::mostliked-project-scroll'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::addvideocomment'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::getvideocomment'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::editvideocomment'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::deletevideocomment'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::checklikestatus'), 'teach::teach')
                 ->add(new Zend_Acl_Resource('teach::teach::allvideocomments'), 'teach::teach')
                ;


        $this->allow('guest', 'teach::teach')
                ->allow('guest', 'teach::teach::teach')
                ->allow('guest', 'teach::teach::teachdetail')
                ->allow('guest', 'teach::teach::teach-ajax-handler')
                ->allow('guest', 'teach::teach::teach-file-handler')
                ->allow('guest', 'teach::teach::teach-video-handler')
                ->allow('guest', 'teach::teach::teach-doc-handler')
                ->allow('guest', 'teach::teach::teach-delete-handler')
                ->allow('guest', 'teach::teach::class-discussion')
                ->allow('guest', 'teach::teach::view-discussion')
                ->allow('guest', 'teach::teach::view-comment')
                ->allow('guest', 'teach::teach::project-comment')
                ->allow('guest', 'teach::teach::teach-class')
                ->allow('user', 'teach::teach::create-projects')
                ->allow('guest', 'teach::teach::show-project-form')
                ->allow('guest', 'teach::teach::show-project')
                ->allow('guest', 'teach::teach::project-likes')
                ->allow('guest', 'teach::teach::project-dislikes')
                ->allow('guest', 'teach::teach::project-comment')
                ->allow('guest', 'teach::teach::project-by-category')
                ->allow('guest', 'teach::teach::image-upload')
                ->allow('guest', 'teach::teach::show-discussion')
                ->allow('guest', 'teach::teach::leave-review')
                ->allow('guest', 'teach::teach::display-discussion')
                ->allow('guest', 'teach::teach::videostatus')
                ->allow('guest', 'teach::teach::loading-vimeourl')
                ->allow('guest', 'teach::teach::teach-cover-image')
                ->allow('user', 'teach::teach::generate-certificate')
                ->allow('user', 'teach::teach::allclasses')
                ->allow('user', 'teach::teach::allclassnext')
                ->allow('user', 'teach::teach::projects')
                ->allow('guest', 'teach::teach::classteach')
                ->allow('guest', 'teach::teach::tooltip')
                ->allow('user', 'teach::teach::certificate')
                ->allow('guest', 'teach::teach::create-project-handler')
                ->allow('guest', 'teach::teach::application')
                ->allow('guest', 'teach::teach::update-social-share')
                 ->allow('guest', 'teach::teach::project-comment')
                 ->allow('guest', 'teach::teach::commentreply')
                 ->allow('guest', 'teach::teach::project-scroll')
                 ->allow('guest', 'teach::teach::recent-project-scroll')
                 ->allow('guest', 'teach::teach::mostliked-project-scroll')
                 ->allow('guest', 'teach::teach::addvideocomment')
                 ->allow('guest', 'teach::teach::getvideocomment')
                 ->allow('guest', 'teach::teach::editvideocomment')
                 ->allow('guest', 'teach::teach::deletevideocomment')
                 ->allow('guest', 'teach::teach::checklikestatus')
                 ->allow('guest', 'teach::teach::allvideocomments')
                ;

//        $this->allow('guest', 'teach::teach')
//                ->allow('guest', 'teach::teach::teach')
//                ->allow('guest', 'teach::teach::teachdetail')
//                ->allow('guest', 'teach::teach::teach-ajax-handler')
//                ->allow('guest', 'teach::teach::teach-file-handler')
//                ->allow('guest', 'teach::teach::teach-video-handler')
//                ->allow('guest', 'teach::teach::teach-doc-handler')
//                ->allow('guest', 'teach::teach::teach-class')
//                ->allow('guest', 'teach::teach::class-discussion')
//                ->allow('guest', 'teach::teach::view-projects')
//                ->allow('guest', 'teach::teach::show-project-form')
//                ->allow('guest', 'teach::teach::show-project')
//                ->allow('guest', 'teach::teach::view-discussion')
//                ->allow('guest', 'teach::teach::view-comment')
//                ->allow('guest', 'teach::teach::project-comment')
//                ->allow('guest', 'teach::teach::create-projects')
//                ->allow('guest', 'teach::teach::image-upload')
//                ->allow('guest', 'teach::teach::show-discussion')
//                ->allow('guest', 'teach::teach::display-discussion')
//                ->allow('guest', 'teach::teach::videostatus')
//                ->allow('guest', 'teach::teach::loading-vimeourl');

        /**
         * Developer: Namrata Singh
         * Settings Module
         */
        $this->add(new Zend_Acl_Resource('settings'))
                ->add(new Zend_Acl_Resource('settings::settings'), 'settings')
                ->add(new Zend_Acl_Resource('settings::settings::profile'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::account'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::password'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::emailnotification'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::password-ajax-handler'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::check-box-ajax-handler'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagar-transaction'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagar-bank-transaction'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagar-postback'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::referrals'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::payment-ajax-handler'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::payments'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagar-error'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::email-refferals'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::refer-fashionlearn'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::notification-email'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::refer-error'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::cancellation'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::delete-ajax-handler'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::creditcard-error'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::payment-error'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::social-connect'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::invoice'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::user-settings'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::delete-card-ajax-handler'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagarbankreq'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagarboleto-userpayment'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::addnew-cardprimary'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::subscription-renewal'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::premium-reactivate'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagarboleto-subscribepayment'), 'settings::settings')
                ->add(new Zend_Acl_Resource('settings::settings::pagarnewcard-transaction'), 'settings::settings');
        

        //    ->add(new Zend_Acl_Resource('teach::teach::teachdetail'), 'teach::teach');


        $this->allow('user', 'settings::settings')
                ->allow('user', 'settings::settings::profile')
                ->allow('user', 'settings::settings::account')
                ->allow('user', 'settings::settings::password')
                ->allow('user', 'settings::settings::emailnotification')
                ->allow('user', 'settings::settings::password-ajax-handler')
                ->allow('user', 'settings::settings::payments')
                ->allow('user', 'settings::settings::payment-ajax-handler')
                ->allow('user', 'settings::settings::pagar-transaction')
                ->allow('user', 'settings::settings::pagar-bank-transaction')
                ->allow('user', 'settings::settings::pagar-postback')
                ->allow('user', 'settings::settings::referrals')
                ->allow('user', 'settings::settings::pagar-error')
                ->allow('user', 'settings::settings::email-refferals')
                ->allow('user', 'settings::settings::refer-fashionlearn')
                ->allow('user', 'settings::settings::notification-email')
                ->allow('user', 'settings::settings::refer-error')
                ->allow('user', 'settings::settings::cancellation')
                ->allow('user', 'settings::settings::delete-ajax-handler')
                ->allow('guest', 'settings::settings::creditcard-error')
                ->allow('guest', 'settings::settings::payment-error')
                ->allow('user', 'settings::settings::social-connect')
                ->allow('guest', 'settings::settings::invoice')
                ->allow('user', 'settings::settings::user-settings')
                ->allow('user', 'settings::settings::delete-card-ajax-handler')
                ->allow('user', 'settings::settings::pagarbankreq')
                ->allow('user', 'settings::settings::pagarboleto-userpayment')
                ->allow('user', 'settings::settings::addnew-cardprimary')
                ->allow('user', 'settings::settings::subscription-renewal')
                ->allow('user', 'settings::settings::premium-reactivate')
                ->allow('user', 'settings::settings::pagarboleto-subscribepayment')
                ->allow('user', 'settings::settings::pagarnewcard-transaction');
        
        
        

        /*
         * Developer: Namrata Singh
         * notifications Module
         */
        $this->add(new Zend_Acl_Resource('notifications'))
                ->add(new Zend_Acl_Resource('notifications::notifications'), 'notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::notifications'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::notifistore'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::click'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::getnotification'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::getmore'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::timezone'), 'notifications::notifications')
                ->add(new Zend_Acl_Resource('notifications::notifications::refresh'), 'notifications::notifications');

        $this->allow('user', 'notifications::notifications')
                ->allow('user', 'notifications::notifications::notifications')
                ->allow('user', 'notifications::notifications::notifistore')
                ->allow('user', 'notifications::notifications::click')
                ->allow('user', 'notifications::notifications::getnotification')
                ->allow('user', 'notifications::notifications::getmore')
                ->allow('user', 'notifications::notifications::timezone')
                ->allow('user', 'notifications::notifications::refresh');




        /* Developer: Namrata Singh
         * Admin Module
         */
        //module::con::action
        $this->add(new Zend_Acl_Resource('admin'))
                ->add(new Zend_Acl_Resource('admin::admin'), 'admin')
                ->add(new Zend_Acl_Resource('admin::admin::dashboard'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::index'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::logout'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::change-password'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::reset-my-password'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::pointsandscores'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::admin-ajax-handler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::achievements'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::levels'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::imageuploadhandler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::reset'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::fashionlearnclub'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::admin::fashioncluborder'), 'admin::admin');

        $this->allow('admin', 'admin::admin::dashboard');
        $this->allow('guest', 'admin::admin::index');
        $this->allow('admin', 'admin::admin::logout');
        $this->allow('admin', 'admin::admin::change-password');
        $this->allow('guest', 'admin::admin::reset-my-password');
        $this->allow('admin', 'admin::admin::pointsandscores');
        $this->allow('admin', 'admin::admin::admin-ajax-handler');
        $this->allow('admin', 'admin::admin::achievements');
        $this->allow('admin', 'admin::admin::levels');       
        $this->allow('admin', 'admin::admin::imageuploadhandler');
        $this->allow('guest', 'admin::admin::reset');
        $this->allow('admin', 'admin::admin::fashionlearnclub');
         $this->allow('guest', 'admin::admin::fashioncluborder');

        //$this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::categorymanagement'), 'admin')
                ->add(new Zend_Acl_Resource('admin::categorymanagement::category-manage'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::categorymanagement::category-ajax-handler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::categorymanagement::edit-category'), 'admin::admin');

        $this->allow('admin', 'admin::categorymanagement::category-manage')
                ->allow('admin', 'admin::categorymanagement::category-ajax-handler')
                ->allow('admin', 'admin::categorymanagement::edit-category');
        
       
               
               
        // $this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::classesdetail'), 'admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::classes-details'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::class-ajax-handler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::edit-class'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::class-units'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::edit-class-units'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::get-transcoded-videos'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::class-unit-videos'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::edit-class-video'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::ordervideo'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::createclass'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::unassignedclasses'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::invitations'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::classesdetail::teachdetail'), 'admin::admin');

        $this->allow('admin', 'admin::classesdetail::classes-details')
                ->allow('admin', 'admin::classesdetail::class-ajax-handler')
                ->allow('admin', 'admin::classesdetail::edit-class')
                ->allow('admin', 'admin::classesdetail::class-units')
                ->allow('admin', 'admin::classesdetail::edit-class-units')
                ->allow('guest', 'admin::classesdetail::get-transcoded-videos')
                ->allow('admin', 'admin::classesdetail::class-unit-videos')
                ->allow('admin', 'admin::classesdetail::edit-class-video')
                ->allow('admin', 'admin::classesdetail::ordervideo')
                 ->allow('admin', 'admin::classesdetail::createclass')
                ->allow('admin', 'admin::classesdetail::unassignedclasses')
                ->allow('admin', 'admin::classesdetail::invitations')
                 ->allow('admin', 'admin::classesdetail::teachdetail');





        // $this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::manageusers'), 'admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::manageusers'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::user-ajax-handler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::edit-user'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::create-user'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::editbankdetails'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::manageusers::pagarbankcrs'), 'admin::admin');



        $this->allow('admin', 'admin::manageusers::manageusers')
                ->allow('admin', 'admin::manageusers::user-ajax-handler')
                ->allow('admin', 'admin::manageusers::edit-user')
                ->allow('admin', 'admin::manageusers::create-user')
                ->allow('admin', 'admin::manageusers::editbankdetails')
                ->allow('admin', 'admin::manageusers::pagarbankcrs');



        //$this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::membershipplans'), 'admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::membership-plans'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::edit-plans'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::create-plans'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::membership-ajax-handler'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::coupons'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::transaction-details'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::create-coupon'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::edit-coupon'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::referral-commission'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::membershipplans::edit-commissionvalues'), 'admin::admin');
                


        $this->allow('admin', 'admin::membershipplans::membership-plans')
                ->allow('admin', 'admin::membershipplans::edit-plans')
                ->allow('admin', 'admin::membershipplans::create-plans')
                ->allow('admin', 'admin::membershipplans::membership-ajax-handler')
                ->allow('admin', 'admin::membershipplans::coupons')
                ->allow('admin', 'admin::membershipplans::transaction-details')
                ->allow('admin', 'admin::membershipplans::create-coupon')
                ->allow('admin', 'admin::membershipplans::edit-coupon')
                ->allow('admin', 'admin::membershipplans::referral-commission')
                ->allow('admin', 'admin::membershipplans::edit-commissionvalues');
                

        // $this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::paymentdetails'), 'admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::payment-details'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::get-paid-students'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::payment-teacher'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::referal-payment'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::teacher-formula'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::teacher-payment'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::payment-process'), 'admin::admin')
                 ->add(new Zend_Acl_Resource('admin::paymentdetails::teachertransactionstatus-cron'), 'admin::admin')
                 ->add(new Zend_Acl_Resource('admin::paymentdetails::getadmin-revenue'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::paymentdetails::getannual-overview'), 'admin::admin')
                 ->add(new Zend_Acl_Resource('admin::paymentdetails::getreferrals-details'), 'admin::admin')
                 ->add(new Zend_Acl_Resource('admin::paymentdetails::referralpayment-process'), 'admin::admin')
                 ->add(new Zend_Acl_Resource('admin::paymentdetails::referralstatus-cron'), 'admin::admin')
                  ->add(new Zend_Acl_Resource('admin::paymentdetails::getmonths-filter'), 'admin::admin');
        



        $this->allow('admin', 'admin::paymentdetails::payment-details')
                ->allow('admin', 'admin::paymentdetails::get-paid-students')
                ->allow('admin', 'admin::paymentdetails::payment-teacher')
                ->allow('admin', 'admin::paymentdetails::referal-payment')
                ->allow('admin', 'admin::paymentdetails::teacher-formula')
                ->allow('guest', 'admin::paymentdetails::teacher-payment')
                ->allow('admin', 'admin::paymentdetails::payment-process')
                ->allow('guest', 'admin::paymentdetails::teachertransactionstatus-cron')
                ->allow('admin', 'admin::paymentdetails::getadmin-revenue')
                ->allow('admin', 'admin::paymentdetails::getannual-overview')
                ->allow('admin', 'admin::paymentdetails::getreferrals-details')
                ->allow('admin', 'admin::paymentdetails::referralpayment-process')
                ->allow('guest', 'admin::paymentdetails::referralstatus-cron')
                ->allow('guest', 'admin::paymentdetails::getmonths-filter');
                
  
         //$this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::payment'), 'admin')
                ->add(new Zend_Acl_Resource('admin::payment::payment-control'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::payment::adminfinance'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::payment::getteacher-paydetails'), 'admin::admin');
                $this->allow('admin', 'admin::payment::payment-control')
                      ->allow('admin', 'admin::payment::adminfinance')
                      ->allow('admin', 'admin::payment::getteacher-paydetails');
        
        //$this->add(new Zend_Acl_Resource('admin'))
        $this->add(new Zend_Acl_Resource('admin::projectdetails'), 'admin')
                ->add(new Zend_Acl_Resource('admin::projectdetails::project-details'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::projectdetails::edit-projects'), 'admin::admin')
                ->add(new Zend_Acl_Resource('admin::projectdetails::project-ajax-handler'), 'admin::admin');


        $this->allow('admin', 'admin::projectdetails::project-details')
                ->allow('admin', 'admin::projectdetails::edit-projects')
                ->allow('admin', 'admin::projectdetails::project-ajax-handler');


        $this->add(new Zend_Acl_Resource('admin::subadmin'), 'admin')
                ->add(new Zend_Acl_Resource('admin::subadmin::create-admin'), 'admin::admin');

        $this->allow('admin', 'admin::subadmin::create-admin');


        /**
         * Cron Module
         */
        $this->add(new Zend_Acl_Resource('cron'))
                ->add(new Zend_Acl_Resource('cron::cron'), 'cron')
                ->add(new Zend_Acl_Resource('cron::cron::manage-subscription'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::free-subscription'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::trailusers-cron'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::postback-url'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::boletotrailusers-cron'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::postbackboleto-url'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::trialboleto-url'), 'cron::cron')
                ->add(new Zend_Acl_Resource('cron::cron::trail-warningdaysurl'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::subscribeduserscard-cron'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::postbackboletoreactivation-url'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::subscribedusersboleto-cron'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::freemiumend-cron'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::warning-creditcard'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::subscribedusers-warningcron'), 'cron::cron')
               ->add(new Zend_Acl_Resource('cron::cron::pagarend-cron'), 'cron::cron');
               
        
        
        


        $this->allow('guest', 'cron::cron::manage-subscription')
                ->allow('guest', 'cron::cron::free-subscription')
                ->allow('guest', 'cron::cron::trailusers-cron')
                ->allow('guest', 'cron::cron::postback-url')
                ->allow('guest', 'cron::cron::boletotrailusers-cron')
                ->allow('guest', 'cron::cron::postbackboleto-url')
                ->allow('guest', 'cron::cron::trialboleto-url')
                ->allow('guest', 'cron::cron::trail-warningdaysurl')
                ->allow('guest', 'cron::cron::subscribeduserscard-cron')
                ->allow('guest', 'cron::cron::postbackboletoreactivation-url')
                ->allow('guest', 'cron::cron::subscribedusersboleto-cron')
                ->allow('guest', 'cron::cron::freemiumend-cron')
                ->allow('guest', 'cron::cron::warning-creditcard')
                ->allow('guest', 'cron::cron::subscribedusers-warningcron')
                ->allow('guest', 'cron::cron::pagarend-cron');
                

        $this->add(new Zend_Acl_Resource('pagar'))
                ->add(new Zend_Acl_Resource('pagar::pagar'), 'pagar')
                ->add(new Zend_Acl_Resource('pagar::pagar::pagar-transaction'), 'pagar::pagar')
                ->add(new Zend_Acl_Resource('pagar::pagar::pagar-bank-transaction'), 'pagar::pagar')
                ->add(new Zend_Acl_Resource('pagar::pagar::pagar-postback'), 'pagar::pagar');

        $this->allow('user', 'pagar::pagar::pagar-transaction')
            ->allow('user', 'pagar::pagar::pagar-bank-transaction')
            ->allow('user', 'pagar::pagar::pagar-postback');
    }

}

?>