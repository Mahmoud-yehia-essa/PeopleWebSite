<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = Language::all()->keyBy('code');

        if ($languages->isEmpty()) {
            $this->command->error('No languages found in the database. Please add languages first.');
            return;
        }

        $data = [
            'sign_in' => [
                'ar' => 'تسجيل الدخول',
                'en' => 'Sign In',
            ],
            'logout' => [
                'ar' => 'تسجيل الخروج',
                'en' => 'Logout',
            ],
            'profile' => [
                'ar' => 'الملف الشخصي',
                'en' => 'Profile',
            ],
            'edit_profile' => [
                'ar' => 'تعديل الحساب',
                'en' => 'Edit Profile',
            ],
            'edit_profile_desc' => [
                'ar' => 'تحديث معلومات الحساب والصور الشخصية والنبذة التعريفية.',
                'en' => 'Update account information, profile pictures and bio.',
            ],
            'change_cover_picture' => [
                'ar' => 'تغيير غلاف الحساب',
                'en' => 'Change cover picture',
            ],
            'first_name' => [
                'ar' => 'الاسم الأول',
                'en' => 'First Name',
            ],
            'last_name' => [
                'ar' => 'اسم العائلة',
                'en' => 'Last Name',
            ],
            'email' => [
                'ar' => 'البريد الإلكتروني',
                'en' => 'Email Address',
            ],
            'phone_number' => [
                'ar' => 'رقم الهاتف',
                'en' => 'Phone Number',
            ],
            'address_and_country' => [
                'ar' => 'العنوان والبلد',
                'en' => 'Address & Country',
            ],
            'address_placeholder' => [
                'ar' => 'مثال: الرياض، المملكة العربية السعودية',
                'en' => 'e.g. Riyadh, Saudi Arabia',
            ],
            'bio_label' => [
                'ar' => 'النبذة التعريفية (Bio)',
                'en' => 'Biography (Bio)',
            ],
            'bio_placeholder' => [
                'ar' => 'أدخل نبذة مختصرة عن مؤهلاتك ورؤيتك...',
                'en' => 'Enter a brief summary of your qualifications and vision...',
            ],
            'change_password_optional' => [
                'ar' => 'تغيير كلمة المرور (اختياري)',
                'en' => 'Change Password (Optional)',
            ],
            'new_password' => [
                'ar' => 'كلمة المرور الجديدة',
                'en' => 'New Password',
            ],
            'confirm_password' => [
                'ar' => 'تأكيد كلمة المرور',
                'en' => 'Confirm Password',
            ],
            'save_changes' => [
                'ar' => 'حفظ التعديلات',
                'en' => 'Save Changes',
            ],
            'signup_tab' => [
                'ar' => 'حساب جديد',
                'en' => 'New Account',
            ],
            'login_tab' => [
                'ar' => 'تسجيل الدخول',
                'en' => 'Sign In',
            ],
            'wisdom_council_title' => [
                'ar' => 'حكماء العالم',
                'en' => 'World Sages',
            ],
            'wisdom_council_subtitle' => [
                'ar' => 'منصة المبدعين والمفكرين',
                'en' => 'Platform for Creators & Thinkers',
            ],
            'email_or_username' => [
                'ar' => 'البريد الإلكتروني أو اسم المستخدم',
                'en' => 'Email or Username',
            ],
            'password' => [
                'ar' => 'كلمة المرور',
                'en' => 'Password',
            ],
            'expertise_field' => [
                'ar' => 'مجال الخبرة',
                'en' => 'Field of Expertise',
            ],
            'choose_expertise' => [
                'ar' => 'اختر مجالك...',
                'en' => 'Choose your field...',
            ],
            'tech_innovation' => [
                'ar' => 'التقنية والابتكار',
                'en' => 'Tech & Innovation',
            ],
            'education_dev' => [
                'ar' => 'التعليم والتطوير',
                'en' => 'Education & Development',
            ],
            'business_econ' => [
                'ar' => 'الأعمال والاقتصاد',
                'en' => 'Business & Economy',
            ],
            'arts_design' => [
                'ar' => 'الفنون والتصميم',
                'en' => 'Arts & Design',
            ],
            'other_field' => [
                'ar' => 'أخرى',
                'en' => 'Other',
            ],
            'phone_number_label' => [
                'ar' => 'رقم الهاتف الجوال',
                'en' => 'Mobile Phone Number',
            ],
            'send_otp_btn' => [
                'ar' => 'إرسال رمز التحقق عبر الواتس آب',
                'en' => 'Send Verification Code via WhatsApp',
            ],
            'enter_otp' => [
                'ar' => 'رمز التحقق المستلم (OTP)',
                'en' => 'Received Verification Code (OTP)',
            ],
            'resend_in' => [
                'ar' => 'إعادة الإرسال خلال',
                'en' => 'Resend in',
            ],
            'resend_otp_btn' => [
                'ar' => 'إعادة أرسال رمز التحقق',
                'en' => 'Resend Verification Code',
            ],
            'login_with_email' => [
                'ar' => 'تسجيل الدخول باستخدام البريد الإلكتروني',
                'en' => 'Login with Email',
            ],
            'forgot_password' => [
                'ar' => 'نسيت كلمة المرور؟',
                'en' => 'Forgot Password?',
            ],
            'remember_me' => [
                'ar' => 'تذكرني',
                'en' => 'Remember Me',
            ],
            'login_submit_btn' => [
                'ar' => 'دخول للمنصة',
                'en' => 'Enter Platform',
            ],
            'signup_submit_btn' => [
                'ar' => 'إنشاء حساب جديد',
                'en' => 'Create New Account',
            ],
            'or_via' => [
                'ar' => 'أو من خلال',
                'en' => 'Or via',
            ],
            'google_login' => [
                'ar' => 'جوجل',
                'en' => 'Google',
            ],
            'facebook_login' => [
                'ar' => 'فيسبوك',
                'en' => 'Facebook',
            ],
            'phone_login' => [
                'ar' => 'الهاتف',
                'en' => 'Phone',
            ],
            'terms_privacy_agreement' => [
                'ar' => 'باستخدامك للمنصة، أنت توافق على :terms و :privacy.',
                'en' => 'By using the platform, you agree to the :terms and :privacy.',
            ],
            'terms_of_use' => [
                'ar' => 'شروط الاستخدام',
                'en' => 'Terms of Use',
            ],
            'privacy_policy' => [
                'ar' => 'سياسة الخصوصية',
                'en' => 'Privacy Policy',
            ],
            'please_enter_phone' => [
                'ar' => 'يرجى إدخال رقم الهاتف الجوال.',
                'en' => 'Please enter mobile phone number.',
            ],
            'sending_otp_status' => [
                'ar' => 'جاري الإرسال...',
                'en' => 'Sending...',
            ],
            'failed_send_otp' => [
                'ar' => 'فشل إرسال الرمز.',
                'en' => 'Failed to send code.',
            ],
            'error_connecting_server' => [
                'ar' => 'حدث خطأ أثناء الاتصال بالخادم.',
                'en' => 'An error occurred while connecting to the server.',
            ],
            'please_send_otp_first' => [
                'ar' => 'يرجى إرسال رمز التحقق أولاً.',
                'en' => 'Please send verification code first.',
            ],
            'please_enter_6digit_otp' => [
                'ar' => 'يرجى إدخال رمز التحقق المكون من 6 أرقام.',
                'en' => 'Please enter the 6-digit verification code.',
            ],
            'verifying_otp_status' => [
                'ar' => 'جاري التحقق...',
                'en' => 'Verifying...',
            ],
            'verification_success' => [
                'ar' => 'تم التحقق بنجاح! جاري توجيهك...',
                'en' => 'Verified successfully! Redirecting...',
            ],
            'failed_verification' => [
                'ar' => 'فشل التحقق.',
                'en' => 'Verification failed.',
            ],
            'invalid_otp_error' => [
                'ar' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                'en' => 'Invalid or expired verification code.',
            ],
            'create_post' => [
                'ar' => 'طرح موضوع جديد',
                'en' => 'Create New Post',
            ],
            'discussion_board' => [
                'ar' => 'مجلس النقاش العام',
                'en' => 'Discussion Board',
            ],
            'wise_committee' => [
                'ar' => 'لجنة الحكماء',
                'en' => 'Sage Committee',
            ],
            'groups' => [
                'ar' => 'المجموعات',
                'en' => 'Groups',
            ],
            'my_network' => [
                'ar' => 'شبكتي',
                'en' => 'My Network',
            ],
            'search' => [
                'ar' => 'البحث',
                'en' => 'Search',
            ],
            'ambassadors' => [
                'ar' => 'نظام سفراء الحكمة',
                'en' => 'Wisdom Ambassadors',
            ],
            'saved_posts' => [
                'ar' => 'المواضيع المحفوظة',
                'en' => 'Saved Posts',
            ],
            'notifications' => [
                'ar' => 'الإشعارات',
                'en' => 'Notifications',
            ],
            'mark_all_read' => [
                'ar' => 'تحديد الكل كمقروء',
                'en' => 'Mark All as Read',
            ],
            'view_all' => [
                'ar' => 'عرض الكل',
                'en' => 'View All',
            ],
            'delete_discussion_post' => [
                'ar' => 'حذف الموضوع النقاشي',
                'en' => 'Delete Discussion Post',
            ],
            'confirm_delete_post_msg' => [
                'ar' => 'هل أنت متأكد من رغبتك في حذف هذا الموضوع نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.',
                'en' => 'Are you sure you want to delete this post permanently? This action cannot be undone.',
            ],
            'confirm_delete' => [
                'ar' => 'تأكيد الحذف',
                'en' => 'Confirm Delete',
            ],
            'cancel' => [
                'ar' => 'إلغاء',
                'en' => 'Cancel',
            ],
            'delete_post' => [
                'ar' => 'حذف الموضوع',
                'en' => 'Delete Post',
            ],
            'pinned_post' => [
                'ar' => 'موضوع مثبت',
                'en' => 'Pinned Post',
            ],
            'featured' => [
                'ar' => 'متميز',
                'en' => 'Featured',
            ],
            'trending_issues' => [
                'ar' => 'قضايا ملحة ورائجة الآن',
                'en' => 'Trending Issues Now',
            ],
            'trending_issues_sub' => [
                'ar' => 'تصفح المواضيع والقضايا الأكثر نقاشاً وتفاعلاً ومشاركة بين حكماء المنصة',
                'en' => 'Browse topics and issues with the most discussion, interaction, and sharing among sages.',
            ],
            'top_rated_members' => [
                'ar' => 'أعضاء حكمة الأعلى تقييماً',
                'en' => 'Top Rated Wisdom Members',
            ],
            'saved_posts_sub' => [
                'ar' => 'مجموعتك الخاصة من الحكم والمساهمات المحفوظة للرجوع إليها لاحقاً.',
                'en' => 'Your personal collection of wisdom and saved contributions for future reference.',
            ],
            'empty_saved_list' => [
                'ar' => 'قائمتك فارغة',
                'en' => 'Your list is empty',
            ],
            'empty_saved_list_desc' => [
                'ar' => 'لم تقم بحفظ أي مواضيع بعد. اضغط على زر الحفظ بجانب مشاركة الموضوع لحفظه هنا.',
                'en' => 'You have not saved any topics yet. Click the save button next to a post to save it here.',
            ],
            'information' => [
                'ar' => 'معلومات',
                'en' => 'Information',
            ],
            'saved_info_desc' => [
                'ar' => 'المواضيع المحفوظة مخزنة بشكل آمن في حسابك. يمكنك إلغاء حفظ أي موضوع في أي وقت بالنقر مرة أخرى على زر "تم الحفظ" في بطاقة المنشور.',
                'en' => 'Saved posts are securely stored in your account. You can unsave any post at any time by clicking the "Saved" button again.',
            ],
            'post_details_title' => [
                'ar' => 'تفاصيل الموضوع والنقاش',
                'en' => 'Topic details and discussion',
            ],
            'post_details_sub' => [
                'ar' => 'تصفح مساهمات حكماء العالم وشارك بالنقاشات الهادفة.',
                'en' => 'Browse the contributions of the world\'s sages and participate in meaningful discussions.',
            ],
            'back_to_home' => [
                'ar' => 'العودة للرئيسية',
                'en' => 'Back to Home',
            ],
            'platform_admin' => [
                'ar' => 'مدير المنصة',
                'en' => 'Platform Admin',
            ],
            'technical_advisor' => [
                'ar' => 'مستشار تقني',
                'en' => 'Technical Advisor',
            ],
            'points' => [
                'ar' => 'النقاط',
                'en' => 'Points',
            ],
            'friend_requests' => [
                'ar' => 'طلبات الصداقة',
                'en' => 'Friend Requests',
            ],
            'suggested_friends' => [
                'ar' => 'أصدقاء مقترحون',
                'en' => 'Suggested Friends',
            ],
            'no_friend_requests' => [
                'ar' => 'لا توجد طلبات صداقة معلقة',
                'en' => 'No pending friend requests',
            ],
            'no_suggestions' => [
                'ar' => 'لا توجد اقتراحات جديدة',
                'en' => 'No new suggestions',
            ],
            'explore_more' => [
                'ar' => 'استكشاف المزيد',
                'en' => 'Explore More',
            ],
            'trending_issues_sidebar' => [
                'ar' => 'قضايا رائجة الآن',
                'en' => 'Trending Issues Now',
            ],
            'most_trending_hashtags' => [
                'ar' => 'أكثر الهاشتاجات تداولاً',
                'en' => 'Trending Hashtags',
            ],
            'no_trending_hashtags' => [
                'ar' => 'لا توجد هاشتاجات متاحة حالياً',
                'en' => 'No hashtags currently available',
            ],
            'trending_groups' => [
                'ar' => 'المجموعات الرائجة',
                'en' => 'Trending Groups',
            ],
            'no_trending_groups' => [
                'ar' => 'لا توجد مجموعات رائجة حالياً',
                'en' => 'No trending groups currently',
            ],
            'view_all_groups' => [
                'ar' => 'عرض كل المجموعات',
                'en' => 'View All Groups',
            ],
            'weekly_sages' => [
                'ar' => 'حكماء الأسبوع',
                'en' => 'Sages of the Week',
            ],
            'points_label' => [
                'ar' => 'نقطة',
                'en' => 'points',
            ],
            'add_friend' => [
                'ar' => 'إضافة صديق',
                'en' => 'Add Friend',
            ],
            'mutual_friend' => [
                'ar' => 'صديق مشترك',
                'en' => 'mutual friend',
            ],
            'mutual_friends' => [
                'ar' => 'أصدقاء مشتركين',
                'en' => 'mutual friends',
            ],
            'comments' => [
                'ar' => 'تعليق',
                'en' => 'comments',
            ],
            'share' => [
                'ar' => 'مشاركة',
                'en' => 'share',
            ],
            'read_more' => [
                'ar' => 'اقرأ المزيد',
                'en' => 'Read More',
            ],
            'points_logs_title' => [
                'ar' => 'سجل تقييمات العضو',
                'en' => 'Member Evaluation Logs',
            ],
            'points_logs_sub' => [
                'ar' => 'تفاصيل النقاط الممنوحة من لجنة الحكماء',
                'en' => 'Details of points awarded by the Sage Committee',
            ],
            'discussion_sages' => [
                'ar' => 'انضم لحكماء العالم',
                'en' => 'Join Sages of the World',
            ],
            'honorary_member' => [
                'ar' => 'عضو حكمة',
                'en' => 'Wisdom Member',
            ],
            'guest_user' => [
                'ar' => 'زائر كريم',
                'en' => 'Guest User',
            ],
            'guest_login' => [
                'ar' => 'سجل دخولك الآن',
                'en' => 'Login Now',
            ],
            'support' => [
                'ar' => 'تأييد',
                'en' => 'Support',
            ],
            'saved' => [
                'ar' => 'تم الحفظ',
                'en' => 'Saved',
            ],
            'save' => [
                'ar' => 'حفظ',
                'en' => 'Save',
            ],
            'share_post' => [
                'ar' => 'مشاركة الموضوع',
                'en' => 'Share Post',
            ],
            'total_votes' => [
                'ar' => 'إجمالي الأصوات',
                'en' => 'Total Votes',
            ],
            'poll_label' => [
                'ar' => 'استطلاع رأي',
                'en' => 'Poll',
            ],
            'video_label' => [
                'ar' => 'فيديو',
                'en' => 'Video',
            ],
            'show_more' => [
                'ar' => 'عرض المزيد',
                'en' => 'Show More',
            ],
            'accept' => [
                'ar' => 'قبول',
                'en' => 'Accept',
            ],
            'reject' => [
                'ar' => 'رفض',
                'en' => 'Reject',
            ],
            'comment_word' => [
                'ar' => 'التعليق',
                'en' => 'Comment',
            ],
            'reply_word' => [
                'ar' => 'الرد',
                'en' => 'Reply',
            ],
            'replies' => [
                'ar' => 'الردود',
                'en' => 'Replies',
            ],
            'write_reply_placeholder' => [
                'ar' => 'اكتب رداً...',
                'en' => 'Write a reply...',
            ],
            'send' => [
                'ar' => 'إرسال',
                'en' => 'Send',
            ],
            'login_to_reply_placeholder' => [
                'ar' => 'سجل دخولك للرد...',
                'en' => 'Login to reply...',
            ],
            'no_comments_yet' => [
                'ar' => 'لا توجد نقاشات بعد. كن أول من يطرح فكرة!',
                'en' => 'No discussions yet. Be the first to start the conversation!',
            ],
            'comment_submit_failed' => [
                'ar' => 'فشل إرسال التعليق.',
                'en' => 'Failed to submit comment.',
            ],
            'comment_submit_error' => [
                'ar' => 'حدث خطأ أثناء إرسال التعليق.',
                'en' => 'An error occurred while submitting comment.',
            ],
            'reply_submit_failed' => [
                'ar' => 'فشل إرسال الرد.',
                'en' => 'Failed to submit reply.',
            ],
            'reply_submit_error' => [
                'ar' => 'حدث خطأ أثناء إرسال الرد.',
                'en' => 'An error occurred while submitting reply.',
            ],
            'comment_like_failed' => [
                'ar' => 'فشل تحديث الإعجاب بالتعليق.',
                'en' => 'Failed to update comment reaction.',
            ],
            'reply_like_failed' => [
                'ar' => 'فشل تحديث الإعجاب بالرد.',
                'en' => 'Failed to update reply reaction.',
            ],
            'topic_discussion_board' => [
                'ar' => 'مجلس نقاش الموضوع',
                'en' => 'Topic Discussion Board',
            ],
            'write_comment_placeholder' => [
                'ar' => 'اكتب تعليقاً أو موضوعاً مضاداً...',
                'en' => 'Write a comment or counter-topic...',
            ],
            'login_to_comment_placeholder' => [
                'ar' => 'يرجى تسجيل الدخول أو إنشاء حساب جديد لتتمكن من التعليق أو الرد...',
                'en' => 'Please login or create a new account to comment or reply...',
            ],
            'wisdom_author' => [
                'ar' => 'صاحب الحكمة',
                'en' => 'Wisdom Author',
            ],
            'wisdom_member' => [
                'ar' => 'عضو حكماء العالم',
                'en' => 'Wisdom Member',
            ],
            'loading_comments' => [
                'ar' => 'جاري تحميل النقاشات...',
                'en' => 'Loading discussions...',
            ],
            'error_loading_comments' => [
                'ar' => 'حدث خطأ أثناء تحميل النقاشات.',
                'en' => 'An error occurred while loading discussions.',
            ],
            'server_connection_failed' => [
                'ar' => 'فشل الاتصال بالخادم.',
                'en' => 'Failed to connect to server.',
            ],
            'out_of_10_stars' => [
                'ar' => 'من 10 درجات',
                'en' => 'out of 10',
            ],
            'view_rating_details' => [
                'ar' => 'عرض تفاصيل التقييم',
                'en' => 'View rating details',
            ],
            'wise_world_board' => [
                'ar' => 'مجلس حكماء العالم',
                'en' => 'Council of Wisdom',
            ],
            'wise_world_board_desc' => [
                'ar' => 'منصة تجمع المبدعين والرواد لمشاركة الآراء ومناقشة الأفكار البناءة لترتقي بالعلم والحكمة.',
                'en' => 'A platform gathering creators and pioneers to share views and discuss constructive ideas to elevate knowledge and wisdom.',
            ],
            'wise_committee_ruling' => [
                'ar' => 'حكم لجنة الحكماء',
                'en' => 'Wisdom Committee Ruling',
            ],
            'wise_scholar' => [
                'ar' => 'حكيم',
                'en' => 'Wise Scholar',
            ],
            'wise_committee' => [
                'ar' => 'حكماء',
                'en' => 'Sages',
            ],
            'trending_issues_now' => [
                'ar' => 'قضايا رائجة الآن',
                'en' => 'Trending Issues Now',
            ],
            'view_all_trending_issues' => [
                'ar' => 'عرض كل القضايا الرائجة',
                'en' => 'View All Trending Issues',
            ],
            'no_pending_friend_requests' => [
                'ar' => 'لا توجد طلبات صداقة معلقة',
                'en' => 'No pending friend requests',
            ],
            'top_rated_scholars' => [
                'ar' => 'أعضاء حكمة الأعلى تقييماً',
                'en' => 'Top Rated Wisdom Members',
            ],
            'wise_rated_posts' => [
                'ar' => 'مواضيع مقيمة من الحكماء',
                'en' => 'Sage-Rated Posts',
            ],
            'wise_rated_posts_desc' => [
                'ar' => 'المواضيع التي حصلت على أعلى التقييمات من لجنة الحكماء',
                'en' => 'Topics that received the highest ratings from the Sage Committee',
            ],
            'loading_notifications' => [
                'ar' => 'جاري تحميل الإشعارات...',
                'en' => 'Loading notifications...',
            ],
            'no_notifications_available_yet' => [
                'ar' => 'لا توجد إشعارات متاحة حالياً',
                'en' => 'No notifications available yet',
            ],
            'no_notifications_desc' => [
                'ar' => 'سنقوم بإشعارك فور ورود أي تفاعل أو نشاط جديد',
                'en' => 'We will notify you as soon as any new activity or interaction arrives',
            ],
            'error_loading_notifications' => [
                'ar' => 'حدث خطأ أثناء تحميل الإشعارات.',
                'en' => 'An error occurred while loading notifications.',
            ],
            'notifications_marked_read' => [
                'ar' => 'تم تحديد الإشعارات كمقروءة',
                'en' => 'Notifications marked as read',
            ],
            'conversations' => [
                'ar' => 'المحادثات',
                'en' => 'Conversations',
            ],
            'login' => [
                'ar' => 'تسجيل الدخول',
                'en' => 'Login',
            ],
            'regular_post' => [
                'ar' => 'منشور عادي',
                'en' => 'Regular Post',
            ],
            'poll_post' => [
                'ar' => 'استطلاع رأي',
                'en' => 'Poll',
            ],
            'post_placeholder' => [
                'ar' => '‏ماذا يدور في ذهنك؟ اطرح موضوعاً للنقاش...',
                'en' => 'What\'s on your mind? Start a discussion...',
            ],
            'poll_question_placeholder' => [
                'ar' => 'اكتب سؤال استطلاع الرأي...',
                'en' => 'Write your poll question...',
            ],
            'poll_option_1' => [
                'ar' => 'الخيار الأول (مطلوب)',
                'en' => 'First option (required)',
            ],
            'poll_option_2' => [
                'ar' => 'الخيار الثاني (مطلوب)',
                'en' => 'Second option (required)',
            ],
            'poll_option_3' => [
                'ar' => 'الخيار الثالث (اختياري)',
                'en' => 'Third option (optional)',
            ],
            'poll_option_4' => [
                'ar' => 'الخيار الرابع (اختياري)',
                'en' => 'Fourth option (optional)',
            ],
            'publish_post' => [
                'ar' => 'نشر الموضوع',
                'en' => 'Publish Post',
            ],
            'poll_no_media_hint' => [
                'ar' => '* استطلاع الرأي لا يحتوي على صور أو فيديو',
                'en' => '* Poll posts do not support images or video',
            ],
            'login_to_post' => [
                'ar' => 'سجل دخولك لتتمكن من مشاركة مواضيع ونقاشات جديدة.',
                'en' => 'Login to share new topics and discussions.',
            ],
            'discussion_council' => [
                'ar' => 'مجلس النقاش والموضوعات',
                'en' => 'Discussion Council',
            ],
            'post_by_author' => [
                'ar' => 'موضوع بقلم',
                'en' => 'Post by',
            ],
            'write_comment_or_counter' => [
                'ar' => 'اكتب تعليقاً أو موضوعاً مضاداً...',
                'en' => 'Write a comment or counter-topic...',
            ],
            'write_reply_input' => [
                'ar' => 'اكتب رداً...',
                'en' => 'Write a reply...',
            ],
            'send_reply' => [
                'ar' => 'إرسال',
                'en' => 'Send',
            ],
            'replies_label' => [
                'ar' => 'الردود',
                'en' => 'Replies',
            ],
            'no_discussions_yet' => [
                'ar' => 'لا توجد نقاشات بعد. كن أول من يطرح فكرة!',
                'en' => 'No discussions yet. Be the first to share an idea!',
            ],
            'loading_discussions' => [
                'ar' => 'جاري تحميل النقاشات...',
                'en' => 'Loading discussions...',
            ],
            'error_loading_discussions' => [
                'ar' => 'حدث خطأ أثناء تحميل النقاشات.',
                'en' => 'An error occurred while loading discussions.',
            ],
            'connection_failed' => [
                'ar' => 'فشل الاتصال بالخادم لتحميل النقاشات.',
                'en' => 'Failed to connect to the server.',
            ],
            'supporters_title' => [
                'ar' => 'المؤيدون للموضوع',
                'en' => 'Topic Supporters',
            ],
            'loading_list' => [
                'ar' => 'جاري تحميل القائمة...',
                'en' => 'Loading list...',
            ],
            'total_supports' => [
                'ar' => 'إجمالي التأييدات',
                'en' => 'Total Supports',
            ],
            'total_likes' => [
                'ar' => 'إجمالي الإعجابات',
                'en' => 'Total Likes',
            ],
            'liked_by_comment' => [
                'ar' => 'المعجبون بـ التعليق',
                'en' => 'Liked by: Comment',
            ],
            'liked_by_reply' => [
                'ar' => 'المعجبون بـ الرد',
                'en' => 'Liked by: Reply',
            ],
            'no_supporters_yet' => [
                'ar' => 'لا يوجد مؤيدون بعد.',
                'en' => 'No supporters yet.',
            ],
            'no_likes_yet' => [
                'ar' => 'لا يوجد معجبون بعد.',
                'en' => 'No likes yet.',
            ],
            'error_loading_supporters' => [
                'ar' => 'حدث خطأ أثناء تحميل المؤيدين.',
                'en' => 'Error loading supporters.',
            ],
            'error_loading_likes' => [
                'ar' => 'حدث خطأ أثناء تحميل المعجبين.',
                'en' => 'Error loading likes.',
            ],
            'member_word' => [
                'ar' => 'عضو',
                'en' => 'Member',
            ],
            'intellectual_advisor' => [
                'ar' => 'مستشار فكري',
                'en' => 'Intellectual Advisor',
            ],
            'login_to_comment' => [
                'ar' => 'يرجى',
                'en' => 'Please',
            ],
            'login_to_comment_link' => [
                'ar' => 'تسجيل الدخول',
                'en' => 'login',
            ],
            'login_to_comment_suffix' => [
                'ar' => 'لتتمكن من إضافة تعليق أو الرد.',
                'en' => 'to add a comment or reply.',
            ],
            'mutual_friends_with' => [
                'ar' => 'الأصدقاء المشتركون مع',
                'en' => 'Mutual Friends with',
            ],
            'mutual_friends_title' => [
                'ar' => 'الأصدقاء المشتركون',
                'en' => 'Mutual Friends',
            ],
            'no_mutual_friends' => [
                'ar' => 'لا يوجد أصدقاء مشتركين',
                'en' => 'No mutual friends',
            ],
            'mutual_friend_label' => [
                'ar' => 'صديق مشترك',
                'en' => 'Mutual friend',
            ],
            'view_profile' => [
                'ar' => 'عرض الملف',
                'en' => 'View Profile',
            ],
            'error_loading_data' => [
                'ar' => 'حدث خطأ أثناء تحميل البيانات.',
                'en' => 'An error occurred while loading data.',
            ],
            'add_new_story' => [
                'ar' => 'إضافة قصة جديدة',
                'en' => 'Add New Story',
            ],
            'story_caption_label' => [
                'ar' => 'نص القصة (اختياري)',
                'en' => 'Story Caption (Optional)',
            ],
            'story_caption_placeholder' => [
                'ar' => 'اكتب نصاً لقصتك هنا...',
                'en' => 'Write a caption for your story...',
            ],
            'story_media_label' => [
                'ar' => 'الوسائط (صورة أو فيديو)',
                'en' => 'Media (Image or Video)',
            ],
            'story_dropzone_click' => [
                'ar' => 'اضغط لاختيار صورة أو فيديو',
                'en' => 'Click to select an image or video',
            ],
            'story_dropzone_drag' => [
                'ar' => 'أو اسحب وأفلت الملف هنا',
                'en' => 'Or drag and drop the file here',
            ],
            'story_file_limit' => [
                'ar' => 'الحد الأقصى: صورة (5MB) أو فيديو (25MB)',
                'en' => 'Max size: image (5MB) or video (25MB)',
            ],
            'story_uploading' => [
                'ar' => 'جاري رفع القصة...',
                'en' => 'Uploading story...',
            ],
            'cancel' => [
                'ar' => 'إلغاء',
                'en' => 'Cancel',
            ],
            'publish_story' => [
                'ar' => 'نشر القصة',
                'en' => 'Publish Story',
            ],
            'story_24h_badge' => [
                'ar' => 'ستوري 24 ساعة',
                'en' => '24h Story',
            ],
            'story_views_title' => [
                'ar' => 'المشاهدات',
                'en' => 'Views',
            ],
            'delete_story_title' => [
                'ar' => 'حذف القصة؟',
                'en' => 'Delete Story?',
            ],
            'delete_story_confirm_msg' => [
                'ar' => 'هل أنت متأكد من رغبتك في حذف هذه القصة نهائياً؟ لا يمكن التراجع عن هذا الإجراء بعد إتمامه.',
                'en' => 'Are you sure you want to permanently delete this story? This action cannot be undone.',
            ],
            'delete_permanent' => [
                'ar' => 'حذف نهائي',
                'en' => 'Delete Permanently',
            ],
            'حيكم مستوى أول' => [
                'ar' => 'حيكم مستوى أول',
                'en' => 'Wise Level 1',
            ],
            'حكيم مستوى أول' => [
                'ar' => 'حكيم مستوى أول',
                'en' => 'Wise Level 1',
            ],
            'حكيم مستوى ثاني' => [
                'ar' => 'حكيم مستوى ثاني',
                'en' => 'Wise Level 2',
            ],
            'current_rank' => [
                'ar' => 'الرتبة الحالية',
                'en' => 'Current Rank',
            ],
            'default_wisdom_user' => [
                'ar' => 'مستخدم حكماء العالم',
                'en' => 'Wisdom User',
            ],
            'send_message' => [
                'ar' => 'إرسال رسالة',
                'en' => 'Send Message',
            ],
            'cancel_request' => [
                'ar' => 'إلغاء الطلب',
                'en' => 'Cancel Request',
            ],
            'accept_friendship' => [
                'ar' => 'قبول الصداقة',
                'en' => 'Accept Friendship',
            ],
            'platform_owner' => [
                'ar' => 'مالك المنصة',
                'en' => 'Platform Owner',
            ],
            'technical_advisor' => [
                'ar' => 'مستشار تقني',
                'en' => 'Technical Advisor',
            ],
            'no_bio_written' => [
                'ar' => 'لا يوجد سيرة ذاتية مكتوبة بعد.',
                'en' => 'No biography written yet.',
            ],
            'topics_count_label' => [
                'ar' => 'المواضيع',
                'en' => 'Topics',
            ],
            'wisdom_points_label' => [
                'ar' => 'نقاط الحكمة',
                'en' => 'Wisdom Points',
            ],
            'friend_count_label' => [
                'ar' => 'صديق',
                'en' => 'friend',
            ],
            'friends_count_label' => [
                'ar' => 'صديق',
                'en' => 'friends',
            ],
            'no_contributions_yet' => [
                'ar' => 'لا توجد مساهمات أو حكم منشورة بعد.',
                'en' => 'No contributions or wisdom posted yet.',
            ],
            'achievements_ratings' => [
                'ar' => 'الإنجازات والتقييمات',
                'en' => 'Achievements & Ratings',
            ],
            'wise_rated_posts' => [
                'ar' => 'مواضيع مقيمة من الحكماء',
                'en' => 'Wise Rated Posts',
            ],
            'no_rated_posts_yet' => [
                'ar' => 'لا توجد مواضيع مقيمة بعد.',
                'en' => 'No rated posts yet.',
            ],
            'points_awarded_posts' => [
                'ar' => 'مواضيع نالت نقاط تقييم',
                'en' => 'Points Awarded Posts',
            ],
            'no_points_posts_yet' => [
                'ar' => 'لا توجد مواضيع نالت نقاطاً بعد.',
                'en' => 'No points awarded posts yet.',
            ],
            'friends_title' => [
                'ar' => 'الأصدقاء',
                'en' => 'Friends',
            ],
            'no_friends_added_yet' => [
                'ar' => 'لا يوجد أصدقاء مضافين بعد.',
                'en' => 'No friends added yet.',
            ],
            'mutual_friend_count' => [
                'ar' => 'مشترك',
                'en' => 'mutual',
            ],
            'joined_groups' => [
                'ar' => 'المجموعات المشترك بها',
                'en' => 'Joined Groups',
            ],
            'no_joined_groups_yet' => [
                'ar' => 'لم يشترك في أي مجموعة بعد.',
                'en' => 'Has not joined any groups yet.',
            ],
            'download_image' => [
                'ar' => 'تحميل الصورة',
                'en' => 'Download Image',
            ],
            'must_login_vote' => [
                'ar' => 'يجب تسجيل الدخول للتصويت في الاستطلاع.',
                'en' => 'You must log in to vote.',
            ],
            'vote_error' => [
                'ar' => 'حدث خطأ أثناء إرسال التصويت.',
                'en' => 'An error occurred while submitting your vote.',
            ],
            'friend_request_error' => [
                'ar' => 'حدث خطأ أثناء إرسال طلب الصداقة.',
                'en' => 'An error occurred while sending friend request.',
            ],
            'cancel_friend_request_error' => [
                'ar' => 'حدث خطأ أثناء إلغاء طلب الصداقة.',
                'en' => 'An error occurred while cancelling friend request.',
            ],
            'friend_request_sent' => [
                'ar' => 'تم إرسال طلب الصداقة بنجاح.',
                'en' => 'Friend request sent successfully.',
            ],
            'cannot_send_friend_request_self' => [
                'ar' => 'لا يمكنك إرسال طلب صداقة لنفسك.',
                'en' => 'You cannot send a friend request to yourself.',
            ],
            'already_friends' => [
                'ar' => 'أنت وهذا المستخدم أصدقاء بالفعل.',
                'en' => 'You are already friends with this user.',
            ],
            'friend_request_pending' => [
                'ar' => 'هناك طلب صداقة معلق بالفعل بينكما.',
                'en' => 'There is already a pending friend request between you.',
            ],
            'friend_request_accepted' => [
                'ar' => 'تم قبول طلب الصداقة بنجاح.',
                'en' => 'Friend request accepted successfully.',
            ],
            'friend_request_cancelled' => [
                'ar' => 'تم إلغاء طلب الصداقة بنجاح.',
                'en' => 'Friend request cancelled successfully.',
            ],
            'request_sent' => [
                'ar' => 'تم إرسال الطلب',
                'en' => 'Request Sent',
            ],
            'no_new_suggestions' => [
                'ar' => 'لا توجد اقتراحات جديدة',
                'en' => 'No new suggestions available',
            ],
            'action_failed' => [
                'ar' => 'فشل تنفيذ الإجراء.',
                'en' => 'Action failed.',
            ],
            'action_error' => [
                'ar' => 'حدث خطأ أثناء تنفيذ الإجراء.',
                'en' => 'An error occurred while performing action.',
            ],
            'wisdom_author' => [
                'ar' => 'كاتب الحكمة',
                'en' => 'Wisdom Author',
            ],
            'post_saved_success' => [
                'ar' => 'تم حفظ الموضوع في قائمتك بنجاح.',
                'en' => 'Post saved to your list successfully.',
            ],
            'post_unsaved_success' => [
                'ar' => 'تم إلغاء حفظ الموضوع بنجاح.',
                'en' => 'Post removed from saved list successfully.',
            ],
            'must_login_to_save' => [
                'ar' => 'يجب تسجيل الدخول أولاً لحفظ المواضيع.',
                'en' => 'You must log in first to save posts.',
            ],
            'wisdom_member' => [
                'ar' => 'عضو بالحكمة',
                'en' => 'Wisdom Member',
            ],
            'unknown_user' => [
                'ar' => 'مستخدم غير معروف',
                'en' => 'Unknown User',
            ],
            'unknown_advisor' => [
                'ar' => 'مستشار غير معروف',
                'en' => 'Unknown Advisor',
            ],
            'view_supporters' => [
                'ar' => 'عرض المؤيدين',
                'en' => 'View Supporters',
            ],
            'view_rating_details' => [
                'ar' => 'عرض تفاصيل التقييم',
                'en' => 'View Rating Details',
            ],
            'out_of_10_stars' => [
                'ar' => 'من 10 درجات',
                'en' => 'out of 10',
            ],
            'wise_scholar' => [
                'ar' => 'حكيم',
                'en' => 'wise scholar',
            ],
            'wise_committee' => [
                'ar' => 'حكماء',
                'en' => 'wise scholars',
            ],
            'wise_committee_ruling' => [
                'ar' => 'حكم وتقييم لجنة الحكماء',
                'en' => 'Sage Committee Ruling',
            ],
            'wise_committee_ruling_title' => [
                'ar' => 'حكم وتقييم لجنة الحكماء',
                'en' => 'Sage Committee Ruling & Rating',
            ],
            'wise_committee_rating_desc' => [
                'ar' => 'تقييم رسمي معتمد وموثق من :count حكماء بالمنصة',
                'en' => 'Official certified rating from :count sages on the platform',
            ],
            'officially_certified_ruling' => [
                'ar' => 'حكم معتمد رسمياً',
                'en' => 'Officially Certified Ruling',
            ],
            'rating_excellent' => [
                'ar' => 'ممتاز',
                'en' => 'Excellent',
            ],
            'rating_good' => [
                'ar' => 'جيد',
                'en' => 'Good',
            ],
            'rating_acceptable' => [
                'ar' => 'مقبول',
                'en' => 'Acceptable',
            ],
            'rating_weak' => [
                'ar' => 'ضعيف',
                'en' => 'Weak',
            ],
            'detailed_ratings_comments_from_sages' => [
                'ar' => 'التقييمات والتعليقات التفصيلية من الحكماء',
                'en' => 'Detailed Ratings & Comments from Sages',
            ],
            'wise_committee_footer_notice' => [
                'ar' => 'هذا التقييم صادر عن لجنة الحكماء المعتمدة رسمياً في منصة حكماء العالم، ويعكس مستوى الجودة والعمق الفكري للموضوع.',
                'en' => 'This rating is issued by the officially certified Sage Committee on Wise World, reflecting the intellectual depth and quality of the topic.',
            ],
            'topic_discussion_board' => [
                'ar' => 'لوحة مناقشة الموضوع والتعليقات',
                'en' => 'Topic Discussion Board & Comments',
            ],
            'write_comment_placeholder' => [
                'ar' => 'أضف تعليقك أو رؤيتك حول هذا الموضوع...',
                'en' => 'Add your comment or vision about this topic...',
            ],
            'loading_comments' => [
                'ar' => 'جاري تحميل التعليقات...',
                'en' => 'Loading comments...',
            ],
            'error_loading_comments' => [
                'ar' => 'حدث خطأ أثناء تحميل التعليقات.',
                'en' => 'An error occurred while loading comments.',
            ],
            'server_connection_failed' => [
                'ar' => 'فشل الاتصال بالخادم.',
                'en' => 'Server connection failed.',
            ],
            'no_comments_yet' => [
                'ar' => 'لا توجد تعليقات حتى الآن. كن أول المشاركين!',
                'en' => 'No comments yet. Be the first to join the discussion!',
            ],
            'comment_submit_failed' => [
                'ar' => 'فشل إرسال التعليق.',
                'en' => 'Failed to submit comment.',
            ],
            'comment_submit_error' => [
                'ar' => 'حدث خطأ أثناء إرسال التعليق.',
                'en' => 'An error occurred while submitting comment.',
            ],
            'reply_submit_failed' => [
                'ar' => 'فشل إرسال الرد.',
                'en' => 'Failed to submit reply.',
            ],
            'reply_submit_error' => [
                'ar' => 'حدث خطأ أثناء إرسال الرد.',
                'en' => 'An error occurred while submitting reply.',
            ],
            'comment_like_failed' => [
                'ar' => 'فشل تسجيل الإعجاب بالتعليق.',
                'en' => 'Failed to react to comment.',
            ],
            'reply_like_failed' => [
                'ar' => 'فشل تسجيل الإعجاب بالرد.',
                'en' => 'Failed to react to reply.',
            ],
            'comment_word' => [
                'ar' => 'التعليق',
                'en' => 'Comment',
            ],
            'reply_word' => [
                'ar' => 'الرد',
                'en' => 'Reply',
            ],
            'wise_world_board' => [
                'ar' => 'منصة حكماء العالم',
                'en' => 'Wise World Board',
            ],
            'wise_world_board_desc' => [
                'ar' => 'مساحة فكرية رائدة لنشر الموضوعات القيمة ومناقشة الرؤى والتجارب الإنسانية الرفيعة.',
                'en' => 'A leading intellectual space for publishing valuable topics and discussing inspiring human visions.',
            ],
            'total_votes_label' => [
                'ar' => 'إجمالي الأصوات',
                'en' => 'Total Votes',
            ],
            'sage_committee_page_title' => [
                'ar' => 'مقر اجتماع لجنة الحكماء | حكماء العالم',
                'en' => 'Sage Committee Chamber | Sages of the World',
            ],
            'open_session' => [
                'ar' => 'جلسة مفتوحة',
                'en' => 'Open Session',
            ],
            'meeting_headquarters' => [
                'ar' => 'مقر اجتماع',
                'en' => 'Meeting Headquarters',
            ],
            'sage_committee_desc' => [
                'ar' => 'هيئة استشارية رفيعة المستوى تضم نخبة من العقول والخبراء المتخصصين، تعمل على تقييم الأفكار وصياغة الرؤى الاستراتيجية بمنهجية الحكمة والخبرة.',
                'en' => 'A high-level advisory body comprising elite minds and specialized experts, working to evaluate ideas and formulate strategic visions with wisdom and experience.',
            ],
            'sage_label' => [
                'ar' => 'حكيم',
                'en' => 'Sage',
            ],
            'specialty_label' => [
                'ar' => 'تخصص',
                'en' => 'Specialty',
            ],
            'no_sages_appointed_yet' => [
                'ar' => 'لم يتم تعيين حكماء بعد',
                'en' => 'No sages appointed yet',
            ],
            'no_sages_appointed_desc' => [
                'ar' => 'سيظهر هنا أعضاء لجنة الحكماء فور تعيينهم من قِبل الإدارة.',
                'en' => 'Wise Committee members will appear here as soon as they are appointed by the administration.',
            ],
            'committee_members_title' => [
                'ar' => 'أعضاء اللجنة',
                'en' => 'Committee Members',
            ],
            'active_members_in_committee' => [
                'ar' => 'عضو نشط في لجنة الحكماء',
                'en' => 'active members in the Sage Committee',
            ],
            'elite_approved_by_admin' => [
                'ar' => 'نخبة مُعتمدة من الإدارة',
                'en' => 'Elite approved by administration',
            ],
            'appointment_date' => [
                'ar' => 'تاريخ التعيين',
                'en' => 'Appointment Date',
            ],
            'role_of_sage_committee' => [
                'ar' => 'دور لجنة الحكماء في منصة حكماء العالم',
                'en' => 'The Role of the Sage Committee in Sages of the World',
            ],
            'role_of_sage_committee_desc' => [
                'ar' => 'تتولى لجنة الحكماء مراجعة المواضيع المطروحة على المنصة وتقييمها بحسب معايير الجودة والعمق الفكري، مما يضمن تميّز المحتوى ويُعلي من مستوى النقاش الموضوعي والاستراتيجي.',
                'en' => 'The Sage Committee is responsible for reviewing and evaluating topics on the platform according to standards of quality and intellectual depth, ensuring content excellence and elevating objective and strategic discussion.',
            ],
            'officially_certified' => [
                'ar' => 'معتمدة رسمياً',
                'en' => 'Officially Certified',
            ],
            'discussion_groups' => [
                'ar' => 'المجموعات النقاشية',
                'en' => 'Discussion Groups',
            ],
            'discussion_groups_desc' => [
                'ar' => 'انضم إلى المجموعات المتخصصة لصناعة الفكر والحلول العملية. تبادل الخبرات وناقش القضايا الكبرى في مساحات مخصصة لمختلف المجالات العلمية والاجتماعية والاقتصادية.',
                'en' => 'Join specialized groups to create thought and practical solutions. Exchange experiences and discuss major issues in spaces dedicated to various scientific, social, and economic fields.',
            ],
            'my_groups' => [
                'ar' => 'مجموعاتي',
                'en' => 'My Groups',
            ],
            'joined' => [
                'ar' => 'منضم إليها',
                'en' => 'Joined',
            ],
            'available_groups' => [
                'ar' => 'المجموعات المتاحة',
                'en' => 'Available Groups',
            ],
            'create_group' => [
                'ar' => 'إنشاء مجموعة',
                'en' => 'Create Group',
            ],
            'no_created_groups_yet' => [
                'ar' => 'لم تقم بإنشاء أي مجموعة بعد',
                'en' => 'You have not created any groups yet',
            ],
            'no_created_groups_desc' => [
                'ar' => 'اضغط على "إنشاء مجموعة" لبدء مجموعتك النقاشية الأولى.',
                'en' => 'Click "Create Group" to start your first discussion group.',
            ],
            'no_joined_groups_desc' => [
                'ar' => 'تصفّح المجموعات المتاحة وانضم إلى ما يناسب اهتماماتك.',
                'en' => 'Browse available groups and join those that match your interests.',
            ],
            'no_groups_registered_yet' => [
                'ar' => 'لا توجد مجموعات نقاشية مسجلة حالياً',
                'en' => 'No discussion groups registered currently',
            ],
            'no_groups_registered_desc' => [
                'ar' => 'يرجى مراجعة الإدارة أو إنشاء مجموعة جديدة لاحقاً.',
                'en' => 'Please check with the administration or create a new group later.',
            ],
            'create_new_discussion_group' => [
                'ar' => 'إنشاء مجموعة نقاشية جديدة',
                'en' => 'Create New Discussion Group',
            ],
            'group_name_label' => [
                'ar' => 'اسم المجموعة *',
                'en' => 'Group Name *',
            ],
            'group_name_placeholder' => [
                'ar' => 'مثال: لجنة الحكمة والعلوم الطبيعية',
                'en' => 'e.g. Wisdom and Natural Sciences Committee',
            ],
            'group_description_label' => [
                'ar' => 'وصف المجموعة',
                'en' => 'Group Description',
            ],
            'group_description_placeholder' => [
                'ar' => 'اكتب نبذة مختصرة عن أهداف ونقاشات المجموعة...',
                'en' => 'Write a short description about the group\'s goals and discussions...',
            ],
            'group_status_label' => [
                'ar' => 'حالة المجموعة *',
                'en' => 'Group Status *',
            ],
            'status_open_desc' => [
                'ar' => 'عامة (مفتوحة للجميع)',
                'en' => 'Public (Open to everyone)',
            ],
            'status_closed_desc' => [
                'ar' => 'خاصة (تتطلب كود انضمام)',
                'en' => 'Private (Requires join code)',
            ],
            'auto_invite_code_notice' => [
                'ar' => 'سيتم توليد كود انضمام تلقائي فريد للمجموعة.',
                'en' => 'A unique join code will be automatically generated for the group.',
            ],
            'group_logo_label' => [
                'ar' => 'صورة المجموعة (الشعار)',
                'en' => 'Group Image (Logo)',
            ],
            'select_logo_label' => [
                'ar' => 'اختر صورة الشعار',
                'en' => 'Select logo image',
            ],
            'max_size_4mb' => [
                'ar' => 'الحجم الأقصى: 4 ميغابايت',
                'en' => 'Max size: 4MB',
            ],
            'group_cover_label' => [
                'ar' => 'صورة الغلاف',
                'en' => 'Cover Image',
            ],
            'select_cover_label' => [
                'ar' => 'اختر صورة الغلاف',
                'en' => 'Select cover image',
            ],
            'save_group_btn' => [
                'ar' => 'حفظ المجموعة',
                'en' => 'Save Group',
            ],
            'join_closed_group_request' => [
                'ar' => 'طلب انضمام لمجموعة خاصة',
                'en' => 'Request to Join Private Group',
            ],
            'join_closed_group_desc' => [
                'ar' => 'هذه المجموعة خاصة وتتطلب كود انضمام للمشاركة في نقاشاتها.',
                'en' => 'This group is private and requires a join code to participate in its discussions.',
            ],
            'enter_join_code' => [
                'ar' => 'أدخل كود الانضمام',
                'en' => 'Enter Join Code',
            ],
            'confirm_join' => [
                'ar' => 'تأكيد الانضمام',
                'en' => 'Confirm Join',
            ],
            'public_group' => [
                'ar' => 'مجموعة عامة',
                'en' => 'Public Group',
            ],
            'private_group' => [
                'ar' => 'مجموعة خاصة',
                'en' => 'Private Group',
            ],
            'no_group_desc_fallback' => [
                'ar' => 'لا يوجد وصف متاح لهذه المجموعة حالياً. انضم وشارك في إثراء المعرفة والتواصل الفعال.',
                'en' => 'No description available for this group currently. Join and participate to enrich knowledge and effective communication.',
            ],
            'member_label' => [
                'ar' => 'عضو',
                'en' => 'member',
            ],
            'topic_label' => [
                'ar' => 'موضوع',
                'en' => 'topic',
            ],
            'admin_prefix' => [
                'ar' => 'مشرف',
                'en' => 'Admin',
            ],
            'enter_discussion_yard' => [
                'ar' => 'دخول ساحة النقاش',
                'en' => 'Enter Discussion Yard',
            ],
            'join_group_btn' => [
                'ar' => 'انضمام للمجموعة',
                'en' => 'Join Group',
            ],
            'request_join_private' => [
                'ar' => 'طلب انضمام (خاصة)',
                'en' => 'Request Join (Private)',
            ],
            'login_to_participate' => [
                'ar' => 'انضم للمشاركة',
                'en' => 'Join to Participate',
            ],
            'server_connection_error' => [
                'ar' => 'حدث خطأ في الاتصال بالخادم.',
                'en' => 'An error occurred while connecting to the server.',
            ],
            'view_cover_image' => [
                'ar' => 'عرض صورة الغلاف',
                'en' => 'View Cover Image',
            ],
            'edit_cover_image' => [
                'ar' => 'تعديل صورة الغلاف',
                'en' => 'Edit Cover Image',
            ],
            'view_group_logo' => [
                'ar' => 'عرض شعار المجموعة',
                'en' => 'View Group Logo',
            ],
            'edit_logo' => [
                'ar' => 'تعديل الشعار',
                'en' => 'Edit Logo',
            ],
            'edit_group_name' => [
                'ar' => 'تعديل اسم المجموعة',
                'en' => 'Edit Group Name',
            ],
            'you_are_group_admin' => [
                'ar' => 'أنت مدير المجموعة',
                'en' => 'You are the Group Admin',
            ],
            'delete_group' => [
                'ar' => 'حذف المجموعة',
                'en' => 'Delete Group',
            ],
            'leave_group' => [
                'ar' => 'مغادرة المجموعة',
                'en' => 'Leave Group',
            ],
            'share_group' => [
                'ar' => 'مشاركة المجموعة',
                'en' => 'Share Group',
            ],
            'posts' => [
                'ar' => 'المنشورات',
                'en' => 'Posts',
            ],
            'members' => [
                'ar' => 'الأعضاء',
                'en' => 'Members',
            ],
            'group_members' => [
                'ar' => 'أعضاء المجموعة',
                'en' => 'Group Members',
            ],
            'founder_and_admin' => [
                'ar' => 'مؤسس ومدير المجموعة',
                'en' => 'Founder & Group Admin',
            ],
            'you' => [
                'ar' => 'أنت',
                'en' => 'You',
            ],
            'delete' => [
                'ar' => 'حذف',
                'en' => 'Delete',
            ],
            'leave' => [
                'ar' => 'مغادرة',
                'en' => 'Leave',
            ],
            'no_group_members_yet' => [
                'ar' => 'لا يوجد أعضاء في هذه المجموعة بعد',
                'en' => 'There are no members in this group yet',
            ],
            'about_group' => [
                'ar' => 'حول المجموعة',
                'en' => 'About Group',
            ],
            'edit_group_desc' => [
                'ar' => 'تعديل وصف المجموعة',
                'en' => 'Edit Group Description',
            ],
            'group_desc_default' => [
                'ar' => 'مساحة فكرية مخصصة لرواد المعرفة والحالمين بمستقبل قائم على الوعي. نناقش هنا قضايا الفلسفة، العلوم، الفن، وتطور الحضارات الإنسانية بأسلوب أكاديمي رصين وروح اجتماعية تفاعلية.',
                'en' => 'An intellectual space dedicated to knowledge pioneers and dreamers of a consciousness-based future. Here we discuss philosophy, science, art, and civilization history in an academic yet interactive social spirit.',
            ],
            'write_group_desc_placeholder' => [
                'ar' => 'اكتب وصفاً للمجموعة...',
                'en' => 'Write a group description...',
            ],
            'public_group_visibility' => [
                'ar' => 'مجموعة عامة - مرئية للجميع',
                'en' => 'Public Group - Visible to everyone',
            ],
            'private_group_visibility' => [
                'ar' => 'مجموعة خاصة - تتطلب كود دعوة',
                'en' => 'Private Group - Requires invitation code',
            ],
            'click_to_copy_code' => [
                'ar' => 'اضغط لنسخ الكود',
                'en' => 'Click to copy code',
            ],
            'join_code_label' => [
                'ar' => 'كود الانضمام',
                'en' => 'Join Code',
            ],
            'founded_in' => [
                'ar' => 'تأسست في',
                'en' => 'Founded in',
            ],
            'group_administration' => [
                'ar' => 'إدارة المجموعة',
                'en' => 'Group Administration',
            ],
            'group_admin_title' => [
                'ar' => 'مدير المجموعة',
                'en' => 'Group Admin',
            ],
            'last_joined_prefix' => [
                'ar' => 'آخر المنضمين',
                'en' => 'Last joined',
            ],
            'topic_title_placeholder' => [
                'ar' => 'عنوان الموضوع النقاشي...',
                'en' => 'Discussion topic title...',
            ],
            'topic_body_placeholder' => [
                'ar' => 'بماذا تفكر يا حكيم اليوم؟ انشر معرفتك...',
                'en' => 'What is on your mind today, Sage? Publish your knowledge...',
            ],
            'attach_media' => [
                'ar' => 'إرفاق صورة/فيديو/صوت',
                'en' => 'Attach Image/Video/Audio',
            ],
            'post_topic_btn' => [
                'ar' => 'نشر الموضوع',
                'en' => 'Publish Topic',
            ],
            'discussion_closed_to_members' => [
                'ar' => 'ساحة النقاش مغلقة للأعضاء فقط',
                'en' => 'Discussion yard is closed to members only',
            ],
            'join_to_view_discussions' => [
                'ar' => 'انضم إلى المجموعة لتتمكن من تصفح المواضيع بالكامل ومشاركة معارفك ونقاشاتك الفكرية مع الأعضاء الآخرين.',
                'en' => 'Join the group to browse all topics and share your knowledge and intellectual discussions with other members.',
            ],
            'join_group_now' => [
                'ar' => 'انضمام للمجموعة الآن',
                'en' => 'Join Group Now',
            ],
            'enter_join_code_btn' => [
                'ar' => 'إدخل كود الانضمام للمجموعة',
                'en' => 'Enter group join code',
            ],
            'login_to_join' => [
                'ar' => 'سجل دخولك للانضمام',
                'en' => 'Log in to join',
            ],
            'discussions_hidden_private_group' => [
                'ar' => 'المواضيع النقاشية مخفية، يرجى الانضمام للمجموعة لرؤية المشاركات.',
                'en' => 'Discussion topics are hidden. Please join the group to see posts.',
            ],
            'no_topics_in_group_yet' => [
                'ar' => 'لا توجد مواضيع نقاشية في هذه المجموعة بعد',
                'en' => 'There are no discussion topics in this group yet',
            ],
            'be_first_to_post_topic' => [
                'ar' => 'كن أول من ينشر موضوعاً فكرياً ومميزاً!',
                'en' => 'Be the first to publish an intellectual and distinguished topic!',
            ],
            'join_private_group_title' => [
                'ar' => 'طلب انضمام لمجموعة خاصة',
                'en' => 'Request to Join Private Group',
            ],
            'join_private_group_desc' => [
                'ar' => 'هذه المجموعة خاصة وتتطلب كود انضمام للمشاركة في نقاشاتها.',
                'en' => 'This group is private and requires a join code to participate in its discussions.',
            ],
            'enter_join_code_placeholder' => [
                'ar' => 'أدخل كود الانضمام',
                'en' => 'Enter Join Code',
            ],
            'confirm_join_btn' => [
                'ar' => 'تأكيد الانضمام',
                'en' => 'Confirm Join',
            ],
            'delete_topic_title' => [
                'ar' => 'حذف الموضوع النقاشي',
                'en' => 'Delete Discussion Topic',
            ],
            'delete_topic_confirm_desc' => [
                'ar' => 'هل أنت متأكد من رغبتك في حذف هذا الموضوع نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.',
                'en' => 'Are you sure you want to delete this topic permanently? This action cannot be undone later.',
            ],
            'confirm_delete_btn' => [
                'ar' => 'تأكيد الحذف',
                'en' => 'Confirm Delete',
            ],
            'leave_group_confirm_desc' => [
                'ar' => 'هل أنت متأكد من رغبتك في مغادرة هذه المجموعة؟ لن تتمكن من المشاركة إلا بالانضمام مجدداً.',
                'en' => 'Are you sure you want to leave this group? You will not be able to participate unless you join again.',
            ],
            'delete_group_forever_title' => [
                'ar' => 'حذف المجموعة نهائياً',
                'en' => 'Delete Group Permanently',
            ],
            'delete_group_forever_confirm_desc' => [
                'ar' => 'هل أنت متأكد من رغبتك في حذف هذه المجموعة بالكامل؟ هذا الإجراء سيؤدي إلى مسح كافة المواضيع، النقاشات، والملفات بشكل نهائي ولا يمكن التراجع عنه.',
                'en' => 'Are you sure you want to delete this group entirely? This action will permanently erase all topics, discussions, and files, and cannot be undone.',
            ],
            'download_image_btn' => [
                'ar' => 'تحميل الصورة',
                'en' => 'Download Image',
            ],
            'cover_image_label' => [
                'ar' => 'صورة الغلاف',
                'en' => 'Cover Image',
            ],
            'group_logo_label' => [
                'ar' => 'شعار المجموعة',
                'en' => 'Group Logo',
            ],
            'kick_member_confirm' => [
                'ar' => 'هل أنت متأكد من حذف العضو ":name" من المجموعة؟',
                'en' => 'Are you sure you want to remove member ":name" from the group?',
            ],
            'leave_group_confirm_simple' => [
                'ar' => 'هل أنت متأكد من مغادرة هذه المجموعة؟',
                'en' => 'Are you sure you want to leave this group?',
            ],
            'fill_required_fields' => [
                'ar' => 'يرجى ملء جميع الحقول المطلوبة.',
                'en' => 'Please fill in all required fields.',
            ],
            'publishing_in_progress' => [
                'ar' => 'جاري النشر...',
                'en' => 'Publishing...',
            ],
            'group_deleted_successfully' => [
                'ar' => 'تم حذف المجموعة بنجاح.',
                'en' => 'Group deleted successfully.',
            ],
            'group_deletion_error' => [
                'ar' => 'حدث خطأ أثناء حذف المجموعة.',
                'en' => 'An error occurred while deleting the group.',
            ],
            'group_left_successfully' => [
                'ar' => 'تمت مغادرة المجموعة بنجاح.',
                'en' => 'Left the group successfully.',
            ],
            'group_leave_error' => [
                'ar' => 'حدث خطأ أثناء محاولة مغادرة المجموعة.',
                'en' => 'An error occurred while leaving the group.',
            ],
            'support_registration_error' => [
                'ar' => 'حدث خطأ أثناء تسجيل التأييد.',
                'en' => 'An error occurred while registering support.',
            ],
            'topic_deletion_error' => [
                'ar' => 'حدث خطأ أثناء محاولة الحذف.',
                'en' => 'An error occurred while attempting deletion.',
            ],
            'topic_published_successfully' => [
                'ar' => 'تم نشر الموضوع بنجاح.',
                'en' => 'Topic published successfully.',
            ],
            'topic_publishing_error' => [
                'ar' => 'حدث خطأ أثناء نشر الموضوع.',
                'en' => 'An error occurred while publishing the topic.',
            ],
            'group_updated_successfully' => [
                'ar' => 'تم تحديث المجموعة بنجاح.',
                'en' => 'Group updated successfully.',
            ],
            'cover_update_error' => [
                'ar' => 'حدث خطأ أثناء تعديل صورة الغلاف.',
                'en' => 'An error occurred while editing the cover photo.',
            ],
            'logo_update_error' => [
                'ar' => 'حدث خطأ أثناء تعديل الشعار.',
                'en' => 'An error occurred while editing the logo.',
            ],
            'group_title_required' => [
                'ar' => 'عنوان المجموعة مطلوب.',
                'en' => 'Group title is required.',
            ],
            'title_update_error' => [
                'ar' => 'حدث خطأ أثناء تعديل العنوان.',
                'en' => 'An error occurred while editing the title.',
            ],
            'desc_update_error' => [
                'ar' => 'حدث خطأ أثناء تعديل الوصف.',
                'en' => 'An error occurred while editing the description.',
            ],
            'invite_code_copied' => [
                'ar' => 'تم نسخ كود الانضمام بنجاح: ',
                'en' => 'Join code copied successfully: ',
            ],
            'saving_in_progress' => [
                'ar' => 'جاري الحفظ...',
                'en' => 'Saving...',
            ],
            'no_group_desc' => [
                'ar' => 'لا يوجد وصف للمجموعة.',
                'en' => 'There is no description for this group.',
            ],
            'special_group' => [
                'ar' => 'مجموعة مميزة',
                'en' => 'Special Group',
            ],
            'join_us_in_group' => [
                'ar' => "انضم إلينا في مجموعة \":group\" على حكماء العالم:\n\":desc\"\n:url",
                'en' => "Join us in the group \":group\" on Wiselook:\n\":desc\"\n:url",
            ],
            'group_share_preview' => [
                'ar' => 'مجموعة ":group" على حكماء العالم...',
                'en' => 'Group ":group" on Wiselook...',
            ],
            'unknown_user' => [
                'ar' => 'مستخدم غير معروف',
                'en' => 'Unknown User',
            ],
            'now' => [
                'ar' => 'الآن',
                'en' => 'Now',
            ],
            'delete_topic' => [
                'ar' => 'حذف الموضوع',
                'en' => 'Delete Topic',
            ],
            'browser_video_unsupported' => [
                'ar' => 'المتصفح الخاص بك لا يدعم تشغيل الفيديو.',
                'en' => 'Your browser does not support video playback.',
            ],
            'supporter' => [
                'ar' => 'مؤيد',
                'en' => 'Supporter',
            ],
            'supporters' => [
                'ar' => 'مؤيدين',
                'en' => 'Supporters',
            ],
            'support_btn' => [
                'ar' => 'تأييد',
                'en' => 'Support',
            ],
            'discussion' => [
                'ar' => 'نقاش',
                'en' => 'Discussion',
            ],
            'my_network_desc' => [
                'ar' => 'تواصل مع الحكماء والخبراء في مجالك وصناع القرار.',
                'en' => 'Connect with sages, experts in your field, and decision makers.',
            ],
            'search_friends_placeholder' => [
                'ar' => 'البحث في الأصدقاء...',
                'en' => 'Search friends...',
            ],
            'all_friends' => [
                'ar' => 'كل الأصدقاء',
                'en' => 'All Friends',
            ],
            'incoming_requests' => [
                'ar' => 'طلبات واردة',
                'en' => 'Incoming Requests',
            ],
            'sent_requests_tab' => [
                'ar' => 'الطلبات المرسلة',
                'en' => 'Sent Requests',
            ],
            'recently_added' => [
                'ar' => 'أُضيفوا حديثاً',
                'en' => 'Recently Added',
            ],
            'network_stats' => [
                'ar' => 'إحصائيات الشبكة',
                'en' => 'Network Stats',
            ],
            'total_friends' => [
                'ar' => 'إجمالي الأصدقاء',
                'en' => 'Total Friends',
            ],
            'new_requests' => [
                'ar' => 'طلبات جديدة',
                'en' => 'New Requests',
            ],
            'active_users' => [
                'ar' => 'مستخدمون نشطون',
                'en' => 'Active Users',
            ],
            'top_rated_members' => [
                'ar' => 'أعضاء حكمة الأعلى تقييماً',
                'en' => 'Top Rated Members',
            ],
            'points_label_with_colon' => [
                'ar' => 'النقاط: :points',
                'en' => 'Points: :points',
            ],
            'request_sent_title' => [
                'ar' => 'تم إرسال الطلب',
                'en' => 'Request Sent',
            ],
            'pending_request_from_member' => [
                'ar' => 'لديك طلب معلق من هذا العضو',
                'en' => 'You have a pending request from this member',
            ],
            'friend_status' => [
                'ar' => 'صديق',
                'en' => 'Friend',
            ],
            'no_records_found' => [
                'ar' => 'لا توجد سجلات',
                'en' => 'No records found',
            ],
            'no_mutual_contacts_between' => [
                'ar' => 'لا توجد جهات اتصال مشتركة بينكما',
                'en' => 'No mutual contacts between you',
            ],
            'error_loading_data' => [
                'ar' => 'حدث خطأ أثناء تحميل البيانات',
                'en' => 'An error occurred while loading data',
            ],
            'loading_data' => [
                'ar' => 'جاري تحميل البيانات...',
                'en' => 'Loading data...',
            ],
            'please_try_again_later' => [
                'ar' => 'يرجى المحاولة مرة أخرى لاحقاً.',
                'en' => 'Please try again later.',
            ],
            'no_contacts_title' => [
                'ar' => 'لا توجد جهات اتصال',
                'en' => 'No Contacts',
            ],
            'no_incoming_requests_msg' => [
                'ar' => 'ليس لديك طلبات صداقة واردة معلقة حالياً.',
                'en' => 'You currently have no pending incoming friend requests.',
            ],
            'no_sent_requests_msg' => [
                'ar' => 'ليس لديك طلبات صداقة مرسلة معلقة حالياً.',
                'en' => 'You currently have no pending sent friend requests.',
            ],
            'no_suggested_friends_msg' => [
                'ar' => 'لا توجد اقتراحات صداقة جديدة حالياً.',
                'en' => 'There are currently no new friend suggestions.',
            ],
            'no_friends_matching_filter' => [
                'ar' => 'لم نجد أي أصدقاء يطابقون خيارات التصفية الحالية.',
                'en' => 'No friends found matching the current filter.',
            ],
            'wisdom_member' => [
                'ar' => 'عضو حكمة',
                'en' => 'Wisdom Member',
            ],
            'point_unit' => [
                'ar' => 'نقطة',
                'en' => 'points',
            ],
            'accept_request' => [
                'ar' => 'قبول الطلب',
                'en' => 'Accept Request',
            ],
            'reject_request' => [
                'ar' => 'رفض الطلب',
                'en' => 'Reject Request',
            ],
            'request_status_pending' => [
                'ar' => 'حالة الطلب: معلق (قيد الانتظار)',
                'en' => 'Request status: Pending',
            ],
            'cancel_request' => [
                'ar' => 'إلغاء الطلب',
                'en' => 'Cancel Request',
            ],
            'send_message' => [
                'ar' => 'إرسال رسالة',
                'en' => 'Send Message',
            ],
            'remove_friend' => [
                'ar' => 'إلغاء الصداقة',
                'en' => 'Remove Friend',
            ],
            'confirm_cancel_friend_request' => [
                'ar' => 'هل أنت متأكد من إلغاء طلب الصداقة؟',
                'en' => 'Are you sure you want to cancel the friend request?',
            ],
            'confirm_remove_friend' => [
                'ar' => 'هل أنت متأكد من إلغاء الصداقة؟',
                'en' => 'Are you sure you want to remove this friend?',
            ],
            'friend_action_error' => [
                'ar' => 'حدث خطأ أثناء تنفيذ الإجراء.',
                'en' => 'An error occurred while executing action.',
            ],
            'ambassadors_desc' => [
                'ar' => 'شارك المعرفة وادعُ الخبراء والمفكرين للانضمام إلى منصة حكماء العالم. بصفتك سفيراً للحكمة، أنت تساهم في إثراء المحتوى الفكري والمجتمعي.',
                'en' => 'Share knowledge and invite experts and thinkers to join the Wiselook platform. As a Wisdom Ambassador, you contribute to enriching intellectual and community content.',
            ],
            'custom_referral_link' => [
                'ar' => 'رابط الإحالة المخصص',
                'en' => 'Custom Referral Link',
            ],
            'copy_link' => [
                'ar' => 'نسخ الرابط',
                'en' => 'Copy Link',
            ],
            'customize_referral_code' => [
                'ar' => 'تخصيص كود الإحالة',
                'en' => 'Customize Referral Code',
            ],
            'customize_referral_code_label' => [
                'ar' => 'كود الإحالة المخصص (يجب أن يحتوي على أحرف وأرقام إنجليزية فقط، بدون مسافات أو رموز خاصة)',
                'en' => 'Custom referral code (must contain English letters and numbers only, no spaces or special characters)',
            ],
            'update_code' => [
                'ar' => 'تحديث الكود',
                'en' => 'Update Code',
            ],
            'total_clicks' => [
                'ar' => 'إجمالي النقرات',
                'en' => 'Total Clicks',
            ],
            'successful_invitations' => [
                'ar' => 'الدعوات الناجحة',
                'en' => 'Successful Invitations',
            ],
            'estimated_reward_points' => [
                'ar' => 'نقاط المكافأة المقدرة',
                'en' => 'Estimated Reward Points',
            ],
            'members_joined_via_link' => [
                'ar' => 'الأعضاء المنضمون مؤخراً عبر رابطك',
                'en' => 'Members recently joined via your link',
            ],
            'joined_prefix' => [
                'ar' => 'انضم :time',
                'en' => 'Joined :time',
            ],
            'platform_admin' => [
                'ar' => 'مدير المنصة',
                'en' => 'Platform Admin',
            ],
            'verified_member' => [
                'ar' => 'عضو موثق',
                'en' => 'Verified Member',
            ],
            'no_referrals_yet' => [
                'ar' => 'لم ينضم أي مستخدمين عبر رابطك بعد. شارك الرابط وابدأ بدعوة الآخرين!',
                'en' => 'No users have joined via your link yet. Share the link and start inviting others!',
            ],
            'referral_link_copied' => [
                'ar' => 'تم نسخ رابط الإحالة بنجاح!',
                'en' => 'Referral link copied successfully!',
            ],
            'copied_btn' => [
                'ar' => 'تم النسخ!',
                'en' => 'Copied!',
            ],
            'search_results' => [
                'ar' => 'نتائج البحث',
                'en' => 'Search Results',
            ],
            'search_results_desc' => [
                'ar' => 'نتائج البحث عن الحكمة والموضوعات ذات الصلة.',
                'en' => 'Search results for wisdom and related topics.',
            ],
            'search_input_placeholder' => [
                'ar' => 'اكتب كلمة البحث هنا...',
                'en' => 'Type search term here...',
            ],
            'searching_results_msg' => [
                'ar' => 'جاري البحث عن النتائج...',
                'en' => 'Searching for results...',
            ],
            'search_tab_all' => [
                'ar' => 'الكل',
                'en' => 'All',
            ],
            'search_tab_people' => [
                'ar' => 'الأشخاص',
                'en' => 'People',
            ],
            'search_tab_topics' => [
                'ar' => 'المواضيع',
                'en' => 'Topics',
            ],
            'search_tab_groups' => [
                'ar' => 'المجموعات',
                'en' => 'Groups',
            ],
            'start_search_title' => [
                'ar' => 'ابدأ البحث في Wiselook',
                'en' => 'Start searching in Wiselook',
            ],
            'start_search_desc' => [
                'ar' => 'ابحث عن مستشارين، أو مواضيع ونقاشات، أو مجموعات تطرح أفكاراً ملهمة.',
                'en' => 'Search for consultants, topics and discussions, or groups that present inspiring ideas.',
            ],
            'no_search_results_title' => [
                'ar' => 'لا توجد نتائج بحث مطابقة',
                'en' => 'No matching search results found',
            ],
            'no_search_results_desc_prefix' => [
                'ar' => 'لم نجد أي نتائج لـ "',
                'en' => 'We found no results for "',
            ],
            'no_search_results_desc_suffix' => [
                'ar' => '". حاول استخدام كلمات دلالية مختلفة.',
                'en' => '". Try using different keywords.',
            ],
            'people_label' => [
                'ar' => 'الأشخاص',
                'en' => 'People',
            ],
            'topics_label' => [
                'ar' => 'المواضيع',
                'en' => 'Topics',
            ],
            'groups_label' => [
                'ar' => 'المجموعات',
                'en' => 'Groups',
            ],
            'member_role' => [
                'ar' => 'عضو',
                'en' => 'Member',
            ],
            'joined_date_prefix' => [
                'ar' => 'انضم في :date',
                'en' => 'Joined on :date',
            ],
            'unknown_user' => [
                'ar' => 'مستخدم غير معروف',
                'en' => 'Unknown User',
            ],
            'show_more' => [
                'ar' => 'عرض المزيد',
                'en' => 'Show more',
            ],
            'video_label' => [
                'ar' => 'فيديو',
                'en' => 'Video',
            ],
            'support_action' => [
                'ar' => 'تأييد',
                'en' => 'Support',
            ],
            'show_supporters' => [
                'ar' => 'عرض المؤيدين',
                'en' => 'Show Supporters',
            ],
            'discussion_label' => [
                'ar' => 'نقاش',
                'en' => 'discussion',
            ],
            'saved_status' => [
                'ar' => 'تم الحفظ',
                'en' => 'Saved',
            ],
            'save_action' => [
                'ar' => 'حفظ',
                'en' => 'Save',
            ],
            'share_post' => [
                'ar' => 'مشاركة الموضوع',
                'en' => 'Share Topic',
            ],
            'member_unit' => [
                'ar' => ':count عضو',
                'en' => ':count member|:count members',
            ],
            'public_label' => [
                'ar' => 'عامة',
                'en' => 'Public',
            ],
            'private_label' => [
                'ar' => 'خاصة',
                'en' => 'Private',
            ],
            'public_group' => [
                'ar' => 'مجموعة عامة',
                'en' => 'Public Group',
            ],
            'private_group' => [
                'ar' => 'مجموعة خاصة',
                'en' => 'Private Group',
            ],
            'filter_results' => [
                'ar' => 'تصفية النتائج',
                'en' => 'Filter Results',
            ],
            'filter_results_desc' => [
                'ar' => 'ابحث عن موضوعات ومجالات الحكمة',
                'en' => 'Search for topics and areas of wisdom',
            ],
            'people_nav' => [
                'ar' => 'الناس',
                'en' => 'People',
            ],
            'all_notifications' => [
                'ar' => 'كل الإشعارات',
                'en' => 'All Notifications',
            ],
            'all_notifications_desc' => [
                'ar' => 'عرض وتتبع جميع الإشعارات والأنشطة الخاصة بحسابك.',
                'en' => 'View and track all notifications and activities of your account.',
            ],
            'mark_all_read' => [
                'ar' => 'تحديد الكل كمقروء',
                'en' => 'Mark all as read',
            ],
            'loading_notifications' => [
                'ar' => 'جاري تحميل الإشعارات...',
                'en' => 'Loading notifications...',
            ],
            'no_notifications' => [
                'ar' => 'لا توجد إشعارات حالياً.',
                'en' => 'No notifications yet.',
            ],
            'error_loading_notifications' => [
                'ar' => 'حدث خطأ أثناء تحميل الإشعارات.',
                'en' => 'An error occurred while loading notifications.',
            ],
            'all_notifications_marked_read' => [
                'ar' => 'تم تحديد جميع الإشعارات كمقروءة.',
                'en' => 'All notifications have been marked as read.',
            ],
            'wisdom_council' => [
                'ar' => 'مجلس الحكمة',
                'en' => 'Wisdom Council',
            ],
            'direct_messages' => [
                'ar' => 'الرسائل المباشرة',
                'en' => 'Direct Messages',
            ],
            'messages_tab' => [
                'ar' => 'الرسائل',
                'en' => 'Messages',
            ],
            'groups_tab' => [
                'ar' => 'المجموعات',
                'en' => 'Groups',
            ],
            'no_messages_yet' => [
                'ar' => 'لا توجد رسائل بعد',
                'en' => 'No messages yet',
            ],
            'no_active_conversations' => [
                'ar' => 'لا توجد محادثات نشطة. يمكنك البحث عن أصدقاء لبدء محادثة.',
                'en' => 'No active conversations. You can search for friends to start a chat.',
            ],
            'create_new_group' => [
                'ar' => 'إنشاء مجموعة جديدة',
                'en' => 'Create new group',
            ],
            'help_and_support' => [
                'ar' => 'المساعدة والدعم',
                'en' => 'Help and Support',
            ],
            'online_now' => [
                'ar' => 'متصل الآن',
                'en' => 'Online now',
            ],
            'search_messages_title' => [
                'ar' => 'بحث في الرسائل',
                'en' => 'Search messages',
            ],
            'direct_call_title' => [
                'ar' => 'اتصال مباشر',
                'en' => 'Direct Call',
            ],
            'group_info_title' => [
                'ar' => 'معلومات المجموعة',
                'en' => 'Group Info',
            ],
            'search_messages_placeholder' => [
                'ar' => 'ابحث في الرسائل...',
                'en' => 'Search messages...',
            ],
            'close_search' => [
                'ar' => 'إغلاق البحث',
                'en' => 'Close search',
            ],
            'uploading_file_status' => [
                'ar' => 'جاري رفع الملف...',
                'en' => 'Uploading file...',
            ],
            'username_placeholder' => [
                'ar' => 'اسم المستخدم',
                'en' => 'Username',
            ],
            'message_content_placeholder' => [
                'ar' => 'محتوى الرسالة...',
                'en' => 'Message content...',
            ],
            'cancel_recording' => [
                'ar' => 'إلغاء التسجيل',
                'en' => 'Cancel recording',
            ],
            'stop_and_send' => [
                'ar' => 'إيقاف وإرسال',
                'en' => 'Stop and send',
            ],
            'type_message_placeholder' => [
                'ar' => 'اكتب رسالتك هنا...',
                'en' => 'Type your message here...',
            ],
            'send_image_title' => [
                'ar' => 'إرسال صورة',
                'en' => 'Send Image',
            ],
            'send_video_title' => [
                'ar' => 'إرسال فيديو',
                'en' => 'Send Video',
            ],
            'send_voice_title' => [
                'ar' => 'إرسال رسالة صوتية',
                'en' => 'Send voice message',
            ],
            'welcome_wisdom_council' => [
                'ar' => 'مرحباً بك في مجلس الحكمة',
                'en' => 'Welcome to the Wisdom Council',
            ],
            'welcome_wisdom_council_desc' => [
                'ar' => 'ابدأ بالتواصل الفوري مع الأصدقاء والحكماء لمناقشة الأفكار وتبادل المعرفة.',
                'en' => 'Start connecting instantly with friends and sages to discuss ideas and exchange knowledge.',
            ],
            'start_new_chat' => [
                'ar' => 'بدء محادثة جديدة',
                'en' => 'Start a new conversation',
            ],
            'group_details_title' => [
                'ar' => 'تفاصيل المجموعة',
                'en' => 'Group Details',
            ],
            'group_details_desc' => [
                'ar' => 'عرض معلومات المجموعة وأعضائها وإدارتها',
                'en' => 'View group information, members and management',
            ],
            'group_name_label' => [
                'ar' => 'اسم المجموعة',
                'en' => 'Group Name',
            ],
            'group_desc_placeholder' => [
                'ar' => 'وصف المجموعة هنا...',
                'en' => 'Group description here...',
            ],
            'group_members_label' => [
                'ar' => 'أعضاء المجموعة',
                'en' => 'Group Members',
            ],
            'create_new_group_desc' => [
                'ar' => 'أنشئ مساحة حوارية جماعية جديدة مع أصدقائك',
                'en' => 'Create a new group discussion space with your friends',
            ],
            'choose_group_image' => [
                'ar' => 'اختر صورة للمجموعة',
                'en' => 'Choose group image',
            ],
            'type_group_name' => [
                'ar' => 'اكتب اسم المجموعة...',
                'en' => 'Type group name...',
            ],
            'group_desc_label' => [
                'ar' => 'وصف المجموعة (اختياري)',
                'en' => 'Group Description (Optional)',
            ],
            'type_group_desc' => [
                'ar' => 'اكتب وصفاً للمجموعة...',
                'en' => 'Type group description...',
            ],
            'choose_members_to_add' => [
                'ar' => 'اختر الأعضاء للإضافة',
                'en' => 'Choose members to add',
            ],
            'search_friend_placeholder' => [
                'ar' => 'ابحث عن صديق...',
                'en' => 'Search for a friend...',
            ],
            'no_active_friends_to_select' => [
                'ar' => 'لا يوجد أصدقاء نشطين للاختيار من بينهم',
                'en' => 'No active friends to select from',
            ],
            'create_group_submit' => [
                'ar' => 'إنشاء المجموعة وبدء المحادثة',
                'en' => 'Create Group & Start Chat',
            ],
            'choose_friend_chat_desc' => [
                'ar' => 'اختر صديقاً لبدء المحادثة معه فوراً',
                'en' => 'Choose a friend to start a conversation with instantly',
            ],
            'search_friend_by_name' => [
                'ar' => "ابحث باسم الصديق...",
                'en' => "Search by friend's name...",
            ],
            'loading_friends' => [
                'ar' => 'جاري تحميل الأصدقاء...',
                'en' => 'Loading friends...',
            ],
            'delete_message_title' => [
                'ar' => 'حذف الرسالة',
                'en' => 'Delete Message',
            ],
            'delete_message_confirm_msg' => [
                'ar' => 'هل أنت متأكد من حذف هذه الرسالة؟ لن تتمكن من استعادتها.',
                'en' => "Are you sure you want to delete this message? You won't be able to recover it.",
            ],
            'yes_delete' => [
                'ar' => 'نعم، احذف',
                'en' => 'Yes, delete',
            ],
            'cancel' => [
                'ar' => 'إلغاء',
                'en' => 'Cancel',
            ],
            'calling_status' => [
                'ar' => 'جاري الاتصال',
                'en' => 'Calling',
            ],
            'decline_call' => [
                'ar' => 'رفض',
                'en' => 'Decline',
            ],
            'accept_call' => [
                'ar' => 'رد',
                'en' => 'Answer',
            ],
            'incoming_call' => [
                'ar' => 'اتصال وارد...',
                'en' => 'Incoming call...',
            ],
            'incoming_group_call' => [
                'ar' => 'اتصال جماعي وارد...',
                'en' => 'Incoming group call...',
            ],
            'incoming_group_call_from' => [
                'ar' => 'اتصال جماعي من: ',
                'en' => 'Incoming group call from: ',
            ],
            'connecting_to_channel' => [
                'ar' => 'جاري الاتصال بالقناة...',
                'en' => 'Connecting to channel...',
            ],
            'call_declined' => [
                'ar' => 'تم رفض المكالمة',
                'en' => 'Call declined',
            ],
            'call_ended' => [
                'ar' => 'انتهت المكالمة',
                'en' => 'Call ended',
            ],
            'agora_not_loaded' => [
                'ar' => 'مكتبة Agora غير محملة.',
                'en' => 'Agora library is not loaded.',
            ],
            'active_status' => [
                'ar' => 'نشط',
                'en' => 'Active',
            ],
            'failed_to_join_call' => [
                'ar' => 'فشل الانضمام للمكالمة: ',
                'en' => 'Failed to join call: ',
            ],
            'choose_friend_first_call' => [
                'ar' => 'يرجى اختيار صديق أو مجموعة أولاً لبدء الاتصال.',
                'en' => 'Please select a friend or a group first to start the call.',
            ],
            'group_call' => [
                'ar' => 'مكالمة جماعية',
                'en' => 'Group Call',
            ],
            'failed_group_call' => [
                'ar' => 'فشل بدء المكالمة الجماعية.',
                'en' => 'Failed to start group call.',
            ],
            'failed_call' => [
                'ar' => 'فشل بدء المكالمة.',
                'en' => 'Failed to start call.',
            ],
            'failed_join_group_call' => [
                'ar' => 'فشل الانضمام للمكالمة الجماعية.',
                'en' => 'Failed to join group call.',
            ],
            'failed_accept_call' => [
                'ar' => 'فشل قبول المكالمة.',
                'en' => 'Failed to accept call.',
            ],
            'audio_muted' => [
                'ar' => 'تم كتم الصوت',
                'en' => 'Audio muted',
            ],
            'audio_unmuted' => [
                'ar' => 'تم تشغيل الصوت',
                'en' => 'Audio unmuted',
            ],
            'active_video' => [
                'ar' => 'نشط (فيديو)',
                'en' => 'Active (Video)',
            ],
            'camera_access_denied' => [
                'ar' => 'لا يمكن الوصول للكاميرا.',
                'en' => 'Cannot access camera.',
            ],
            'yesterday' => [
                'ar' => 'أمس',
                'en' => 'Yesterday',
            ],
            'image_label' => [
                'ar' => 'صورة',
                'en' => 'Image',
            ],
            'video_label' => [
                'ar' => 'فيديو',
                'en' => 'Video',
            ],
            'voice_message_label' => [
                'ar' => 'رسالة صوتية',
                'en' => 'Voice Message',
            ],
            'you_label' => [
                'ar' => 'أنت',
                'en' => 'You',
            ],
            'failed_send_message' => [
                'ar' => 'فشل إرسال الرسالة، يرجى المحاولة مرة أخرى.',
                'en' => 'Failed to send message, please try again.',
            ],
            'searching_friends' => [
                'ar' => 'جاري البحث عن الأصدقاء...',
                'en' => 'Searching for friends...',
            ],
            'no_matching_active_friends' => [
                'ar' => 'لا يوجد أصدقاء نشطين مطابقين.',
                'en' => 'No matching active friends.',
            ],
            'failed_fetch_friends' => [
                'ar' => 'فشل جلب الأصدقاء.',
                'en' => 'Failed to fetch friends.',
            ],
            'typing_indicator' => [
                'ar' => 'يكتب الآن...',
                'en' => 'Typing...',
            ],
            'offline_now' => [
                'ar' => 'غير متصل',
                'en' => 'Offline',
            ],
            'no_group_messages_yet' => [
                'ar' => 'لا توجد رسائل في هذه المجموعة بعد. اكتب رسالة لبدء الحوار.',
                'en' => 'No messages in this group yet. Type a message to start the conversation.',
            ],
            'error_loading_previous_messages' => [
                'ar' => 'خطأ أثناء تحميل الرسائل السابقة.',
                'en' => 'Error occurred while loading previous messages.',
            ],
            'loading_groups' => [
                'ar' => 'جاري تحميل المجموعات...',
                'en' => 'Loading groups...',
            ],
            'no_active_groups' => [
                'ar' => 'لا توجد مجموعات نشطة بعد.',
                'en' => 'No active groups yet.',
            ],
            'failed_loading_groups' => [
                'ar' => 'فشل تحميل المجموعات.',
                'en' => 'Failed to load groups.',
            ],
            'enter_group_name_warn' => [
                'ar' => 'يرجى إدخال اسم المجموعة.',
                'en' => 'Please enter group name.',
            ],
            'choose_at_least_one_member_warn' => [
                'ar' => 'يرجى اختيار عضو واحد على الأقل للمجموعة.',
                'en' => 'Please select at least one member for the group.',
            ],
            'creating_group_status' => [
                'ar' => 'جاري إنشاء المجموعة...',
                'en' => 'Creating group...',
            ],
            'group_created_success' => [
                'ar' => 'تم إنشاء المجموعة بنجاح!',
                'en' => 'Group created successfully!',
            ],
            'group_creation_failed' => [
                'ar' => 'فشل إنشاء المجموعة.',
                'en' => 'Failed to create group.',
            ],
            'unexpected_error_group_creation' => [
                'ar' => 'حدث خطأ غير متوقع أثناء إنشاء المجموعة.',
                'en' => 'An unexpected error occurred while creating group.',
            ],
            'loading_data' => [
                'ar' => 'جاري تحميل البيانات...',
                'en' => 'Loading data...',
            ],
            'no_group_desc' => [
                'ar' => 'لا يوجد وصف للمجموعة.',
                'en' => 'No description for the group.',
            ],
            'creator_role' => [
                'ar' => 'المنشئ',
                'en' => 'Creator',
            ],
            'admin_role' => [
                'ar' => 'مشرف',
                'en' => 'Admin',
            ],
            'member_role' => [
                'ar' => 'عضو',
                'en' => 'Member',
            ],
            'remove_member_title' => [
                'ar' => 'إزالة العضو',
                'en' => 'Remove Member',
            ],
            'delete_group_permanently' => [
                'ar' => 'حذف المجموعة نهائياً',
                'en' => 'Delete Group Permanently',
            ],
            'leave_group' => [
                'ar' => 'مغادرة المجموعة',
                'en' => 'Leave Group',
            ],
            'failed_group_details' => [
                'ar' => 'فشل جلب تفاصيل المجموعة.',
                'en' => 'Failed to fetch group details.',
            ],
            'failed_conn_group_details' => [
                'ar' => 'فشل الاتصال بالخادم لجلب تفاصيل المجموعة.',
                'en' => 'Failed to connect to server for group details.',
            ],
            'confirm_remove_member' => [
                'ar' => 'هل أنت متأكد من إزالة هذا العضو من المجموعة؟',
                'en' => 'Are you sure you want to remove this member from the group?',
            ],
            'member_removed_success' => [
                'ar' => 'تم إزالة العضو بنجاح.',
                'en' => 'Member removed successfully.',
            ],
            'member_removal_failed' => [
                'ar' => 'فشل إزالة العضو.',
                'en' => 'Failed to remove member.',
            ],
            'error_removing_member' => [
                'ar' => 'حدث خطأ أثناء محاولة إزالة العضو.',
                'en' => 'An error occurred while trying to remove the member.',
            ],
            'confirm_leave_group' => [
                'ar' => 'هل أنت متأكد من مغادرة هذه المجموعة؟',
                'en' => 'Are you sure you want to leave this group?',
            ],
            'group_leave_success' => [
                'ar' => 'تمت مغادرة المجموعة بنجاح.',
                'en' => 'Left the group successfully.',
            ],
            'group_leave_failed' => [
                'ar' => 'فشل مغادرة المجموعة.',
                'en' => 'Failed to leave the group.',
            ],
            'error_leaving_group' => [
                'ar' => 'حدث خطأ أثناء محاولة مغادرة المجموعة.',
                'en' => 'An error occurred while trying to leave the group.',
            ],
            'confirm_delete_group' => [
                'ar' => 'هل أنت متأكد تماماً من حذف هذه المجموعة نهائياً؟ سيتم مسح كافة الرسائل للأعضاء أيضاً.',
                'en' => 'Are you sure you want to delete this group permanently? All messages will be deleted for all members as well.',
            ],
            'group_deleted_success' => [
                'ar' => 'تم حذف المجموعة نهائياً بنجاح.',
                'en' => 'Group has been permanently deleted successfully.',
            ],
            'group_delete_failed' => [
                'ar' => 'فشل حذف المجموعة.',
                'en' => 'Failed to delete the group.',
            ],
            'error_deleting_group' => [
                'ar' => 'حدث خطأ أثناء محاولة حذف المجموعة.',
                'en' => 'An error occurred while trying to delete the group.',
            ],
            'group_label' => [
                'ar' => 'المجموعة',
                'en' => 'Group',
            ],
            'original_msg_not_found' => [
                'ar' => 'الرسالة الأصلية غير متوفرة في هذه المحادثة.',
                'en' => 'Original message is not available in this chat.',
            ],
            'uploading_large_video_status' => [
                'ar' => 'جاري معالجة ورفع الفيديو الكبير (قد يستغرق بعض الوقت)...',
                'en' => 'Processing and uploading large video (may take some time)...',
            ],
            'uploading_video_status' => [
                'ar' => 'جاري رفع الفيديو...',
                'en' => 'Uploading video...',
            ],
            'uploading_image_status' => [
                'ar' => 'جاري رفع الصورة...',
                'en' => 'Uploading image...',
            ],
            'processing_cropping_status' => [
                'ar' => 'جاري المعالجة والقص على الخادم...',
                'en' => 'Processing and cropping on server...',
            ],
            'saving_image_status' => [
                'ar' => 'جاري حفظ الصورة على الخادم...',
                'en' => 'Saving image on server...',
            ],
            'video_too_large_error' => [
                'ar' => 'حجم الفيديو كبير جداً. الحد الأقصى المسموح به هو 100 ميجابايت.',
                'en' => 'Video size is too large. Maximum allowed size is 100 MB.',
            ],
            'video_large_warning' => [
                'ar' => 'حجم الفيديو كبير نسبياً (${fileSizeMB} ميجابايت)، قد يستغرق الرفع بضع لحظات.',
                'en' => 'Video size is relatively large (${fileSizeMB} MB), upload may take a few moments.',
            ],
            'browser_audio_not_supported' => [
                'ar' => 'متصفحك لا يدعم تسجيل الصوت.',
                'en' => 'Your browser does not support audio recording.',
            ],
            'mic_permission_required' => [
                'ar' => 'يرجى السماح بالوصول إلى الميكروفون لتسجيل الرسائل الصوتية.',
                'en' => 'Please allow microphone access to record voice messages.',
            ],
            'uploading_voice_status' => [
                'ar' => 'جاري رفع الرسالة الصوتية...',
                'en' => 'Uploading voice message...',
            ],
            'failed_send_voice' => [
                'ar' => 'فشل إرسال الرسالة الصوتية.',
                'en' => 'Failed to send voice message.',
            ],
            'no_matching_search_results' => [
                'ar' => 'لا توجد نتائج مطابقة للبحث.',
                'en' => 'No matching search results.',
            ],
            'error_searching_messages' => [
                'ar' => 'خطأ أثناء البحث عن الرسائل.',
                'en' => 'Error occurred while searching messages.',
            ],
            'error_loading_messages_for_search' => [
                'ar' => 'خطأ أثناء تحميل الرسائل للبحث.',
                'en' => 'Error occurred while loading messages for search.',
            ],
            'deleting_status' => [
                'ar' => 'جاري الحذف...',
                'en' => 'Deleting...',
            ],
            'message_deleted_success' => [
                'ar' => 'تم حذف الرسالة بنجاح.',
                'en' => 'Message deleted successfully.',
            ],
            'message_delete_failed' => [
                'ar' => 'فشل الحذف، حاول مجدداً.',
                'en' => 'Delete failed, please try again.',
            ],
            'members_count_label' => [
                'ar' => 'أعضاء',
                'en' => 'members',
            ],
            'group_audio_call' => [
                'ar' => 'اتصال صوتي جماعي',
                'en' => 'Group Audio Call',
            ],
            'file_size_prefix' => [
                'ar' => 'الحجم: ',
                'en' => 'Size: ',
            ],
            'file_size_suffix_mb' => [
                'ar' => 'ميجابايت',
                'en' => 'MB',
            ],
            'ampm_pm' => [
                'ar' => 'م',
                'en' => 'PM',
            ],
            'ampm_am' => [
                'ar' => 'ص',
                'en' => 'AM',
            ],
            'delete_btn_title' => [
                'ar' => 'حذف',
                'en' => 'Delete',
            ],
            'reply_btn_title' => [
                'ar' => 'رد',
                'en' => 'Reply',
            ],
        ];

        foreach ($data as $key => $translations) {
            foreach ($translations as $langCode => $value) {
                if (isset($languages[$langCode])) {
                    $lang = $languages[$langCode];
                    
                    // Add if not exists
                    Translation::updateOrCreate(
                        [
                            'language_id' => $lang->id,
                            'key' => $key,
                        ],
                        [
                            'value' => $value,
                        ]
                    );

                    // Clear cache for this language
                    cache()->forget("translations_{$langCode}");
                }
            }
        }

        $this->command->info('Translations seeded successfully!');
    }
}
