<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">حكماء العالم</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
        </div>
     </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{route('dashboard')}}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i>
                </div>
                <div class="menu-title">الرئيسية</div>
            </a>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-user'></i>
                </div>
                <div class="menu-title"> إدارة المستخدمين</div>
            </a>
            <ul>
                <li> <a href="{{route('add.user')}}"><i class='bx bx-radio-circle'></i>إضافة مستخدم جديد</a>
                </li>
                <li> <a href="{{route('all.users')}}"><i class='bx bx-radio-circle'></i>عرض المستخدمين</a>
                </li>
                <li> <a href="{{route('all.admin')}}"><i class='bx bx-radio-circle'></i>عرض المديرين</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-detail'></i>
                </div>
                <div class="menu-title">إدارة المواضيع</div>
            </a>
            <ul>
                <li> <a href="{{route('add.post')}}"><i class='bx bx-radio-circle'></i>إضافة موضوع جديد</a>
                </li>
                <li> <a href="{{route('all.posts')}}"><i class='bx bx-radio-circle'></i>عرض كل المواضيع</a>
                </li>
                <li> <a href="{{route('all.saved_posts')}}"><i class='bx bx-radio-circle'></i>المواضيع المحفوظة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-group'></i>
                </div>
                <div class="menu-title">إدارة علاقات الصداقة</div>
            </a>
            <ul>
                <li> <a href="{{route('all.friendships')}}"><i class='bx bx-radio-circle'></i>عرض علاقات الصداقة</a>
                </li>
                <li> <a href="{{route('add.friendship')}}"><i class='bx bx-radio-circle'></i>إضافة علاقة جديدة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-hive'></i>
                </div>
                <div class="menu-title">إدارة المجموعات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.groups')}}"><i class='bx bx-radio-circle'></i>عرض المجموعات</a>
                </li>
                <li> <a href="{{route('add.group')}}"><i class='bx bx-radio-circle'></i>إضافة مجموعة جديدة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-globe'></i>
                </div>
                <div class="menu-title">المجموعات الخاصة والعامة</div>
            </a>
            <ul>
                <li> <a href="{{route('all.group_sites')}}"><i class='bx bx-radio-circle'></i>عرض المجموعات الخاصة والعامة</a>
                </li>
                <li> <a href="{{route('add.group_site')}}"><i class='bx bx-radio-circle'></i>إضافة مجموعة جديدة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-font'></i>
                </div>
                <div class="menu-title">إدارة اللغات والترجمات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.languages')}}"><i class='bx bx-radio-circle'></i>عرض اللغات المتاحة</a>
                </li>
                <li> <a href="{{route('add.language')}}"><i class='bx bx-radio-circle'></i>إضافة لغة جديدة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-images'></i>
                </div>
                <div class="menu-title">إدارة القصص (Stories)</div>
            </a>
            <ul>
                <li> <a href="{{route('all.stories')}}"><i class='bx bx-radio-circle'></i>عرض القصص</a>
                </li>
                <li> <a href="{{route('add.story')}}"><i class='bx bx-radio-circle'></i>إضافة قصة جديدة</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-link-external'></i>
                </div>
                <div class="menu-title">التسويق بالعمولة (Affiliate)</div>
            </a>
            <ul>
                <li> <a href="{{route('all.affiliates')}}"><i class='bx bx-radio-circle'></i>روابط التسويق بالعمولة</a>
                </li>
                <li> <a href="{{route('all.affiliate_trackings')}}"><i class='bx bx-radio-circle'></i>سجل الإحالات والتسجيلات</a>
                </li>
                <li> <a href="{{route('add.affiliate')}}"><i class='bx bx-radio-circle'></i>إنشاء رابط جديد</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <i class='bx bx-award'></i>
                </div>
                <div class="menu-title">الرتب والمستويات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.rankings')}}"><i class='bx bx-radio-circle'></i>إدارة رتب الموقع</a>
                </li>
                <li> <a href="{{route('users.rankings')}}"><i class='bx bx-radio-circle'></i>رتب ومستويات المستخدمين</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="{{ route('report.view') }}">
                <div class="parent-icon"><i class='bx bx-bar-chart-alt-2'></i>
                </div>
                <div class="menu-title">التقارير والمتابعة</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.notifications.create') }}">
                <div class="parent-icon"><i class='bx bx-bell'></i>
                </div>
                <div class="menu-title">الإشعارات</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.global_search') }}">
                <div class="parent-icon"><i class='bx bx-search-alt'></i>
                </div>
                <div class="menu-title">البحث الشامل</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.support_tickets.index') }}">
                <div class="parent-icon"><i class='bx bx-message-square-detail'></i>
                </div>
                <div class="menu-title">التواصل مع المستخدمين</div>
            </a>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bxs-user-detail'></i>
                </div>
                <div class="menu-title">لجنة الحكماء</div>
            </a>
            <ul>
                <li> <a href="{{ route('admin.wise_committees.index') }}"><i class='bx bx-radio-circle'></i>غرفة الاجتماع والمقر</a>
                </li>
                <li> <a href="{{ route('admin.wise_committees.ratings') }}"><i class='bx bx-radio-circle'></i>تقييم مواضيع الأعضاء</a>
                </li>
                <li> <a href="{{ route('admin.wise_committees.member_ratings') }}"><i class='bx bx-radio-circle'></i>تقييم الأعضاء</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-cog'></i>
                </div>
                <div class="menu-title">الإعدادات</div>
            </a>
            <ul>
                <li> <a href="{{ route('admin.app_versions.index') }}"><i class='bx bx-radio-circle'></i>إصدارات التطبيق</a>
                </li>
                <li> <a href="{{ route('admin.app_versions.create') }}"><i class='bx bx-radio-circle'></i>إضافة إصدار جديد</a>
                </li>
            </ul>
        </li>
    </ul>
    <!--end navigation-->
</div>
