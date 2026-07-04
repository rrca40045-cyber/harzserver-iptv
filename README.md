# سيرفر Xtream Codes متوافق — دليل التركيب

## 1. رفع الملفات
ارفع كل الملفات (`config.php`, `player_api.php`, `get.php`, `live.php`, `.htaccess`, `import_m3u.php`) لمجلد `public_html` (أو `htdocs`) فـ InfinityFree/GoogieHost.

## 2. إنشاء قاعدة البيانات
1. من لوحة تحكم الاستضافة، دخل لـ **MySQL Databases** وأنشئ قاعدة بيانات.
2. دخل لـ **phpMyAdmin**، اختر القاعدة، وشغل محتوى `schema.sql` (Import أو SQL tab).

## 3. تعديل `config.php`
بدل هاد القيم بلي عندك فـ لوحة التحكم:
- `DB_HOST` (غالباً `sqlXXX.epizy.com` أو شبيه)
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `SERVER_URL` (الدومين ديالك، مثلاً `harziptv.site.je`)

## 4. تفعيل باسوورد تسجيل الدخول (مهم قبل ما تستعمل الموقع!)
1. زور: `https://harziptv.site.je/generate_password_hash.php?pass=كلمة_السر_الجديدة_ديالك`
2. نسخ الهاش اللي غيبان ليك.
3. حطو فـ `config.php` مكان `ADMIN_PASSWORD_HASH`.
4. بدل `ADMIN_USERNAME` بأي اسم بغيتي.
5. **امسح `generate_password_hash.php` من السيرفر** بعد ما تخلص، حيت كيبقى خطر إلا بقا.

## 5. استيراد القنوات
- ارفع ملف الـ M3U ديالك للسيرفر (مثلاً `playlist.m3u`)، أو خلي الرابط ديال المصدر.
- زور: `https://harziptv.site.je/login.php` ودخل بالمعلومات اللي دايرها.
- بعد الدخول غتوصل أوتوماتيكيًا لـ `import_m3u.php` — دوز الرابط بزيادة `?source=playlist.m3u`:
  `https://harziptv.site.je/import_m3u.php?source=playlist.m3u`
- إلا بغيتي من رابط خارجي: `import_m3u.php?source=https://example.com/list.m3u`
- عاود هاد الخطوة كل مرة كتحدث القنوات (خاصك تكون داخل بالسيسيون، إلا خرجات دخل من `login.php`).

## 6. ربط المشتركين الحاليين
جدول `subscribers` فـ `schema.sql` فيه نفس البنية اللي شفتها فـ بانل `harziptv` ديالك (username, password, expire_date, status). إلا عندك جدول ديجا بأسماء مختلفة، بدل الاستعلامات فـ `player_api.php` و `get.php` باش تطابق الأعمدة ديالك.

## 7. الإعدادات فـ التطبيق (IPTV Smarters / TiviMate)
```
Server URL: https://harziptv.site.je
Username: [نفس username ديال المشترك]
Password: [نفس password ديال المشترك]
```

---

## 🚂 التركيب على Railway (بديل عن الاستضافة التقليدية)

### 1. رفع الملفات لـ GitHub
1. من جوالك، افتح [github.com/new](https://github.com/new) وأنشئ repository جديد (مثلاً `harzserver-iptv`).
2. من صفحة الـ repo، اضغط **"uploading an existing file"** وارفع كل الملفات (كاملين، بما فيهم `Procfile`, `composer.json`, `nixpacks.toml`, `index.php`).
3. اضغط **Commit changes**.

### 2. إنشاء قاعدة البيانات فـ Railway
1. رجع لـ Railway، فـ نفس المشروع اضغط **"Database"** ثم اختار **MySQL**.
2. Railway غيولد الاتصال أوتوماتيكياً (Host, User, Password, Database) — مايحتاجش تكتبهم يدوياً.

### 3. ربط كود GitHub
1. اضغط **"+ New"** فنفس المشروع، اختار **GitHub Repository**.
2. اختار الـ repo اللي رفعتي (`harzserver-iptv`).
3. Railway غيبدا يبني وينشر تلقائياً.

### 4. ربط متغيرات قاعدة البيانات بالكود
1. دخل لخدمة الكود (مو خدمة الـ MySQL)، روح لتبويب **Variables**.
2. اضغط **"+ New Variable"** → **"Add Reference"** واختار متغيرات MySQL وحدة وحدة:
   `MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`
   (Railway كيسهل هاد الخطوة بزر "Add all" غالباً)
3. `config.php` مبرمج يقرا هاد المتغيرات تلقائياً.

### 5. توليد دومين عمومي
1. فخدمة الكود، روح لتبويب **Settings** → **Networking** → **Generate Domain**.
2. هادا هو الدومين اللي غادي تستعملو فـ `Server URL` (مثال: `harzserver-iptv-production.up.railway.app`).

### 6. تشغيل schema.sql
1. دخل لخدمة MySQL فـ Railway، روح لتبويب **Data**.
2. استعمل الـ Query console وحط محتوى `schema.sql` كامل، نفذو.

### 7. كمل نفس خطوات install.php وimport_m3u.php
نفس الخطوات اللي فوق (install.php لتحديد باسوورد الأدمين، import_m3u.php لاستيراد القنوات) — غير بدل الدومين بالدومين ديال Railway الجديد.


- تأكد أن `mod_rewrite` مفعّل فـ الاستضافة (اختياري الآن، فقط إذا استعملت `live.php` + `.htaccess`).
- **مشكل bot detection**: بعض الاستضافات المجانية (InfinityFree, GoogieHost, ByetHost) عندها حماية (WAF/edge) كتحجب طلبات تطبيقات IPTV (TiviMate, IPTV Smarters) رغم أن الرابط كيخدم عادي فالمتصفح. إلا صادفتك رسالة "Failed to load channels" رغم أن `get.php` كيرجع محتوى صحيح (تأكد بـ `debug_get.php`)، فالمشكل فالاستضافة نفسها.
  - **الحل:** جرب استضافة بلا هاد الحماية، بحال:
    - VPS رخيص (Contabo, Hetzner, DigitalOcean) — أضمن حل
    - Render.com / Railway.app (free tier، أخف من InfinityFree family)
    - تجنب كل الاستضافات المبنية على شبكة "byet/infinityfree/googiehost" حيت كلهم عندهم نفس البنية التحتية والحماية.
- الروابط ديال البث الأصلية (`stream_url` فـ جدول channels) خاصها تكون شغالة وما محجوبة بـ hotlink protection.
- إلا كنت تستعمل `live.php` (رابط بصيغة `/live/username/password/id.ts`)، خاصك `.htaccess` مفعّل. الافتراضي الحالي في `get.php` يعطي الروابط الأصلية مباشرة بلا وسيط، وهو الأبسط والأضمن.
