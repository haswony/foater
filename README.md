# 💰 Debt & Installment Management System (Multi-Tenant SaaS)

نظام احترافي لإدارة الديون والأقساط، **متعدد المتاجر (Multi-Tenant)** — يمكن لمالك النظام بيعه لعدة متاجر، بحيث تُعزل بيانات كل متجر تماماً عن غيره. مبني بـ **PHP Native** + **SQLite** بنمط **MVC**، مع واجهة عربية حديثة **RTL** متجاوبة.

## 🏢 بنية المتاجر المتعددة

- **المدير العام (Super Admin):** يضيف متاجر جديدة ويعطي كل متجر اسم مستخدم وكلمة مرور.
- **مدير المتجر (Admin):** يدير زبائن وديون ومستخدمين متجره فقط.
- **الموظف (Employee):** يعمل ضمن متجر واحد بصلاحيات محدودة.
- كل البيانات (زبائن، ديون، دفعات، أقساط، إعدادات) مفصولة عبر `tenant_id` على مستوى قاعدة البيانات.

---

## ✨ المميزات

### إدارة الزبائن
- ✅ إضافة / تعديل / حذف / بحث
- ✅ سجل ديون كل زبون
- ✅ تتبّع آخر دفعة لكل زبون

### إدارة الديون والدفعات
- ✅ ديون كاملة أو على أقساط (أسبوعي / شهري)
- ✅ توليد جدول أقساط تلقائي
- ✅ تسجيل الدفعات والتوزيع التلقائي على الأقساط
- ✅ حساب المتبقي تلقائياً
- ✅ تذكيرات بالأقساط القادمة
- ✅ تلوين الزبائن المتأخرين بالأحمر

### الذكاء والتحليل
- ✅ تقييم الزبون: **ملتزم / متوسط / خطر**
- ✅ تحليل سلوك الدفع (نسبة التأخر، الأقساط المتأخرة)
- ✅ اقتراحات تلقائية ("زبون جيد" / "لا تعطه دين جديد")

### التقارير
- ✅ Dashboard احترافية: إجمالي الديون / المدفوع / المتبقي / المتأخرات
- ✅ إحصائيات شهرية + رسم بياني
- ✅ أكثر الزبائن مديونية
- ✅ تصدير Excel/CSV (UTF-8 BOM)

### المستندات والطباعة
- ✅ وصل دفع جاهز للطباعة
- ✅ عقد دين كامل (HTML → طباعة كـ PDF من المتصفح)

### الأمان والمستخدمون
- ✅ نظام تسجيل دخول مع كلمات مرور مشفّرة (`bcrypt`)
- ✅ صلاحيات: مدير / موظف
- ✅ حماية CSRF + Prepared Statements ضد SQL Injection
- ✅ هروب الإخراج ضد XSS
- ✅ جلسات HttpOnly + SameSite

---

## 📋 المتطلبات

- PHP **7.4+** (يفضّل 8.x)
- إضافات: `pdo`, `pdo_sqlite`, `mbstring`
- متصفح حديث (Chrome / Edge / Firefox)

---

## 🚀 طريقة التشغيل

### الخيار 1: السيرفر المدمج بـ PHP (أسرع طريقة للاختبار)

```bash
# في مجلد المشروع
php -S localhost:8000 -t public
```

ثم افتح المتصفح على:
- **التثبيت لأول مرة:** http://localhost:8000/../install.php  
  أو شغّل من مجلد الجذر: `php install.php` (قم بفتحه عبر سيرفر يخدم الجذر)
- **النظام:** http://localhost:8000

> ملاحظة: للسيرفر المدمج، استخدم هذا الأمر من الجذر للوصول لـ `install.php` مباشرة:
> ```bash
> php -S localhost:8000
> ```
> ثم زر `http://localhost:8000/install.php` ثم `http://localhost:8000/public/`.

### الخيار 2: Apache / XAMPP / Laragon

1. ضع المشروع في `htdocs`
2. زر `http://localhost/foater/install.php` لإنشاء قاعدة البيانات
3. ثم زر `http://localhost/foater/public/`
4. (اختياري) احذف `install.php` بعد التثبيت

### الخيار 3: Nginx

اجعل `public/` هو document root، ثم وجّه كل الطلبات إلى `index.php`:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## 🔑 بيانات دخول المدير العام (Super Admin)

| الحقل | القيمة |
|-------|--------|
| اسم المستخدم | `superadmin` |
| كلمة المرور | `super123` |

> هذا حساب **مالك النظام** — يستخدم لإضافة وإدارة المتاجر فقط، ولا يصل لبيانات أي متجر.

### إضافة متجر جديد
1. سجّل دخول كـ`superadmin`
2. اذهب إلى **المتاجر → إضافة متجر**
3. أدخل اسم المتجر + بيانات مدير المتجر (username/password)
4. أعطِ بيانات الدخول لصاحب المتجر

⚠️ **قم بتغيير كلمة مرور المدير العام فوراً** من "ملفي الشخصي" بعد أول دخول.

---

## 📂 هيكل المشروع

```
foater/
├── app/
│   ├── Core/                    # المحرك الأساسي
│   │   ├── Database.php         # PDO Wrapper
│   │   ├── Router.php           # موجّه المسارات
│   │   ├── Controller.php       # أصل المتحكمات
│   │   ├── Auth.php             # المصادقة
│   │   ├── CustomerAnalyzer.php # محرك تحليل الزبائن
│   │   └── Helpers.php          # دوال مساعدة
│   ├── Controllers/             # المتحكمات
│   ├── Models/                  # الموديلات
│   └── Views/                   # القوالب (RTL)
│       ├── layouts/             # main, auth, print
│       ├── auth/, dashboard/, customers/
│       └── debts/, payments/, reports/, users/
├── database/
│   ├── schema.sql               # هيكل القاعدة
│   └── data.sqlite              # تُنشأ تلقائياً
├── public/                      # نقطة الدخول
│   ├── index.php                # Front Controller
│   ├── .htaccess
│   └── assets/
│       ├── style.css
│       └── app.js
├── config.php                   # إعدادات النظام
├── install.php                  # ملف التثبيت
└── README.md
```

---

## 🗄️ قاعدة البيانات

7 جداول مترابطة بعلاقات `FOREIGN KEY` مع `ON DELETE CASCADE`:

| الجدول | الوصف |
|--------|------|
| `tenants` | المتاجر (Multi-Tenancy) |
| `users` | المستخدمون (super_admin / admin / employee) |
| `customers` | الزبائن (مرتبط بـ `tenant_id`) |
| `debts` | الديون (مرتبط بـ `tenant_id`) |
| `installments` | الأقساط |
| `payments` | الدفعات |
| `settings` | إعدادات كل متجر (مفتاح-قيمة لكل tenant) |

كل الاستعلامات على بيانات المتاجر تتم تلقائياً عبر `Tenant::id()` لضمان عدم تسرّب أي بيانات بين المتاجر.

---

## 🛡️ الأمان

| الحماية | الطريقة |
|--------|---------|
| SQL Injection | PDO Prepared Statements 100% |
| XSS | دالة `e()` (htmlspecialchars) في جميع المخرجات |
| CSRF | توكن لكل جلسة + تحقق في كل POST |
| Session Hijacking | HttpOnly + SameSite=Lax + `session_regenerate_id` |
| كلمات المرور | `password_hash` (bcrypt) |
| Brute Force | _أضف rate-limiter حسب الحاجة_ |

---

## 🎨 التقنيات

- **Backend:** PHP Native (MVC مخصص)
- **Database:** SQLite 3 (مع WAL mode)
- **Frontend:** Bootstrap 5 RTL, Bootstrap Icons, Chart.js
- **JS:** Vanilla JavaScript (Fetch API)

---

## 🔧 إعدادات سريعة

عدّل `config.php` لتخصيص:
- اسم النظام والعملة
- مسار قاعدة البيانات
- المنطقة الزمنية
- عدد أيام التأخر

من واجهة قاعدة البيانات (جدول `settings`)، يمكنك تعديل:
- اسم المحل / الهاتف / العنوان (تظهر على الوصولات والعقود)
- عدد أيام التذكير قبل الاستحقاق

---

## 🐛 استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| `لم يتم تثبيت النظام بعد` | شغّل `install.php` |
| `unable to open database file` | تأكد من صلاحيات الكتابة على مجلد `database/` |
| الروابط مكسورة في Apache | تأكد من تفعيل `mod_rewrite` |
| الواجهة بدون تنسيق | تأكد من اتصال الإنترنت (Bootstrap من CDN) |

---

## 🌐 النشر على استضافة (Production Deployment)

### خطوات النشر على استضافة مشتركة (Shared Hosting)

1. **رفع الملفات:**
   - ارفع كل الملفات ما عدا `.gitignore` وملفات الاختبار
   - تأكد أن مجلد `database/` قابل للكتابة (chmod 755 أو 777)

2. **تشغيل التثبيت:**
   - افتح الرابط: `https://yourdomain.com/install.php`
   - سيتم إنشاء قاعدة البيانات وحساب المدير العام
   - بعد نجاح التثبيت، **احذف ملف `install.php** فوراً** للأمان

3. **إعدادات الإنتاج:**
   - افتح `config.php` وغيّر:
     ```php
     'debug' => false,          // لإخفاء الأخطاء من المستخدمين
     'force_https' => true,     // بعد تفعيل SSL
     ```

4. **تغيير كلمة المرور الافتراضية:**
   - سجّل دخول كـ `superadmin` / `super123`
   - غيّر كلمة المرور فوراً من "ملفي الشخصي"

5. **حماية إضافية (اختياري):**
   - استخدم كلمة مرور قوية لحساب superadmin
   - فعّل SSL (HTTPS) من لوحة تحكم الاستضافة
   - أضف حماية ضد DDoS (Cloudflare مثلاً)

### خطوات النشر على VPS (DigitalOcean / Hetzner / etc.)

1. **تثبيت PHP والملحقات:**
   ```bash
   sudo apt update
   sudo apt install php php-sqlite3 php-mbstring php-xml php-curl
   ```

2. **تثبيت Nginx:**
   ```bash
   sudo apt install nginx
   ```

3. **إعداد Nginx:**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/foater/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.x-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       # حماية ملفات SQLite
       location ~ \.sqlite$ {
           deny all;
       }
   }
   ```

4. **رفع الملفات وتشغيل التثبيت** (كما في الاستضافة المشتركة)

---

## ⚠️ تنبيهات مهمة

- **بعد التثبيت، احذف `install.php` فوراً** — هذا ملف حساس جداً
- **غيّر كلمة مرور superadmin** قبل إعطاء النظام للعملاء
- **تفعيل HTTPS** ضروري لحماية كلمات المرور
- **عمل نسخة احتياطية** دورية لملف `database/data.sqlite`
- **لا ترفع `database/data.sqlite`** إلى GitHub (محمي بـ `.gitignore`)

---

## 📜 الترخيص

مفتوح المصدر — استخدمه وعدّله بحرية.
