# 🎓 Academic Organizer 
**منصة رقمية متكاملة لإدارة الوقت وتنظيم المهام الدراسية للطلاب الجامعيين.**

---

## 📌 نظرة عامة عن المشروع (Project Overview)

**Academic Organizer** هو نظام برمجيات كخدمة (SaaS) يعمل عبر الإنترنت بالكامل دون الحاجة لتثبيته على الأجهزة[cite: 1]. يهدف النظام إلى تحسين إدارة الوقت، تنظيم المهام الدراسية، تعزيز التواصل بين أطراف العملية التعليمية، وتقليل التوتر والعبء الذهني للطلاب الجامعيين[cite: 1].

*   **الفئة المستهدفة:** طلاب وطالبات الجامعات في المملكة العربية السعودية، أعضاء هيئة التدريس، وأولياء الأمور[cite: 1].

## 🛠️ المتطلبات التقنية وبيئة التشغيل (System Requirements)

### أولاً: المتطلبات البرمجية (Software Requirements)
*   **لغات التطوير الأساسية:** PHP (للعمليات الديناميكية والخادم)، HTML5 ،CSS3 (لتصميم واجهات المستخدم)[cite: 1].
*   **بيئة التطوير (IDE):** Visual Studio Code[cite: 1].
*   **إدارة قواعد البيانات:** MySQL عبر نظام phpMyAdmin[cite: 1].
*   **الخادم المحلي للتطوير:** بيئة XAMPP (Apache Server)[cite: 1].
*   **أنظمة التشغيل المدعومة:** متوافق مع جميع الأجهزة (Any Device)، تم التطوير والاختبار على نظام Windows[cite: 1].
*   **أدوات المساعدة والذكاء الاصطناعي المستعملة:** Uizard, Locofy.ai, Builder.io, ChatGPT, Gemini[cite: 1].

### ثانياً: متطلبات الأجهزة الأدنى لتشغيل الخادم (Hardware Requirements)
| المتطلب | الحد الأدنى للمواصفات |
| :--- | :--- |
| **المعالج (Processor)** | بقوة 1.4 GHz (64-bit) أو أعلى[cite: 1] |
| **الذاكرة العشوائية (RAM)** | 512 MB كحد أدنى[cite: 1] |
| **المساحة التخزينية (Disk Space)** | 32 GB أو أكثر[cite: 1] |
| **دقة العرض (Display)** | $800 \times 600$ بكسل كحد أدنى[cite: 1] |

---

## 🚀 المتطلبات الوظيفية وصلاحيات المستخدمين (Functional Requirements)

تم تصميم النظام بدعم 4 أدوار مختلفة بصلاحيات مخصصة لكل فئة[cite: 1]:

### 1. حساب الطالب (Student)
*   إنشاء حساب جديد، تسجيل الدخول، وتعديل الملف الشخصي مع تشفير كلمة المرور لحماية البيانات[cite: 1].
*   استعراض الجدول الدراسي الأسبوعي (Class Schedule)[cite: 1].
*   إضافة، تحديث، وحذف المهام أو الواجبات الأكاديمية (Tasks / Assignments)[cite: 1].
*   استقبال تنبيهات وإشعارات باقتراب مواعيد التسليم والأحداث الهامة[cite: 1].
*   مشاركة الجدول والمهام الأكاديمية مع زملائه لتعزيز العمل الجماعي المشترك[cite: 1].
*   إرسال دعوة بريد إلكتروني لولي الأمر لربط الحساب ومتابعة الأداء الدراسي[cite: 1].
*   **نظام التحفيز (Gamification):** نظام يمنح نقاطاً وبطاقات ملونة عند إنجاز المهام في وقتها المحدد[cite: 1].
*   **مقررات الجامعة (University Courses):** اختيار الكلية لاستعراض المقررات المتاحة وجداولها وإضافتها تلقائياً للجدول الخاص بالطالب[cite: 1].
*   استعراض تقارير تحليل الأداء (Performance Analysis) لمعرفة نسبة الإنجاز والمهام المتأخرة[cite: 1].

### 2. حساب عضو هيئة التدريس (Faculty Member)
*   إنشاء حساب، تسجيل الدخول، وإدارة المقررات التي يقوم بتدريسها خلال الفصل الدراسي[cite: 1].
*   بناء وإعداد الجداول الدراسية الخاصة بالمحاضرات (مواعيد وأيام المقررات)[cite: 1].
*   إنشاء الواجبات والمهام الأكاديمية للطلاب، تحديد نوعها (فردي/جماعي)، ووضع تاريخ ووقت التسليم[cite: 1].
*   إرسال التنبيهات والإعلانات للطلاب المشرف عليهم[cite: 1].
*   رصد وتحديث درجات الطلاب وتقديم التقييمات والملاحظات (Feedback)[cite: 1].

### 3. حساب ولي الأمر (Parent / Guardian)
*   استقبال وقبول أو رفض دعوات الربط المرسلة من الأبناء[cite: 1].
*   استعراض قائمة الأبناء المرتبطين بالحساب[cite: 1].
*   متابعة الأداء الدراسي التفصيلي للابن (المهام المكتملة وغير المكتملة، ونسب الإنجاز لكل مادة)[cite: 1].
*   استعراض رسوم بيانية وتقارير إحصائية توضح تقدم الطالب طوال الفصل الدراسي[cite: 1].

### 4. حساب مدير النظام (Admin)
*   تسجيل الدخول ومراقبة استخدام النظام وأدائه العام[cite: 1].
*   إدارة حسابات المستخدمين (طلاب، أعضاء هيئة تدريس، أولياء أمور) وتعديلها أو حذفها[cite: 1].
*   إدارة وتحديث الجداول الأكاديمية العامة والإعلانات والإشعارات على المنصة[cite: 1].

---

## 🔒 المتطلبات غير الوظيفية (Non-Functional Requirements)

*   **التوفر (Availability):** استمرارية عمل الموقع بأعلى كفاءة لضمان وصول المستخدمين لبياناتهم في أي وقت[cite: 1].
*   **الأمان والحماية (Security):** تشفير كلمات المرور باستخدام تقنية Hashing لمنع الاختراقات وحماية خصوصية البيانات[cite: 1].
*   **سهولة الاستخدام (Usability):** واجهات بسيطة وسهلة ومريحة تناسب مختلف المستويات التقنية للمستخدمين[cite: 1].
*   **المرونة والنقل (Portability):** إمكانية تصفح الموقع والوصول لكافة وظائفه من مختلف الأجهزة والمنصات[cite: 1].
*   **الأداء (Performance):** سرعة استجابة الموقع وتحميل الصفحات بفضل البنية البرمجية المهيكلة بشكل جيد[cite: 1].
*   **مواجهة القيود (Limitations Handling):** بناء خاصية تتيح العمل الجزئي (تحميل المحتوى مسبقاً) لضمان الكفاءة عند ضعف الإنترنت، وتوفير نسخة خفيفة للعمل على الأجهزة ذات الإمكانيات المحدودة[cite: 1].

---

## 🗄️ هيكل قاعدة البيانات (Database Schema)

يعتمد النظام على قاعدة بيانات متكاملة مبنية بعلاقات (One-to-Many و Many-to-Many) متمثلة في الجداول التالية[cite: 1]:

*   `users`: تخزين بيانات المستخدمين، الأدوار، وتفاصيل الحسابات[cite: 1].
*   `university`: قائمة الجامعات المتوفرة بالنظام[cite: 1].
*   `college`: أسماء الكليات التابعة لكل جامعة[cite: 1].
*   `course`: المقررات الدراسية المتاحة والمضافة[cite: 1].
*   `schedule`: مواعيد المحاضرات والأيام الأسبوعية[cite: 1].
*   `task`: المهام، التوصيف، المواعيد النهائية، وحالة الإنجاز[cite: 1].
*   `taskuser` / `scheduleuser`: جداول وسيطة لإدارة المهام والجداول المشتركة والمشتركة بين المستخدمين[cite: 1].
*   `student_guardian`: إدارة طلبات وحالات الربط بين الطلاب وأولياء أمورهم[cite: 1].
*   `notification`: سجلات الرسائل والتنبيهات المتبادلة في النظام[cite: 1].

---
## 📸 لقطات شاشة من المنصة (Screenshots)

### 🌐 الواجهات العامة وصفحات التعريف (Public Pages)

#### 1. الصفحة الرئيسية (Main Page)
*Main page interface: This page is the welcome page of the system. It introduces the user to the Academic Organizer platform and guides them to log in or create a new account to start using the website.*
<img width="922" height="420" alt="home" src="https://github.com/user-attachments/assets/7d1d5aa6-cd3c-426f-9013-191b1a3b9150" />

#### 2. صفحة إنشاء حساب جديد (Registration Page)
<img width="1920" height="869" alt="Register" src="https://github.com/user-attachments/assets/297f6e9e-8030-42c0-b4b6-5cd336d5f467" />

#### 3. صفحة تسجيل الدخول (Login Page)
<img width="1920" height="879" alt="Login" src="https://github.com/user-attachments/assets/01be7c0b-67fb-4bde-8c4f-60d2418eb13e" />

#### 4. صفحة من نحن (About Us)
<img width="1569" height="712" alt="About" src="https://github.com/user-attachments/assets/b460c7cd-84f3-471f-90bc-dea4a80fb478" />

#### 5. صفحة اتصل بنا (Contact Us)
<img width="1633" height="794" alt="contact" src="https://github.com/user-attachments/assets/03623cb6-eb3d-4a50-a6bb-85022d94ab98" />

---

### 🎓 أولاً: حساب وبوابة الطالب (Student Portal Dashboard)

#### 1. رسالة الترحيب والواجهة الرئيسية للطالب (Welcome Home)
<img width="1280" height="556" alt="welcome home" src="https://github.com/user-attachments/assets/305c09c0-d855-477e-84c9-1246e3a8941e" />

#### 2. الملف الشخصي للطالب (Student Profile)
<img width="1280" height="701" alt="Students profile" src="https://github.com/user-attachments/assets/9e1124be-c058-4eca-9cb7-d8a47338b7b0" />

#### 3. استعراض المقررات الدراسية (Courses View)
<img width="1280" height="583" alt="Coruse" src="https://github.com/user-attachments/assets/202c32bd-971e-4eb3-94c5-8a3d040091cd" />

#### 4. الجدول الدراسي الأسبوعي (Class Schedule)[cite: 1]
<img width="1280" height="588" alt="Schedule" src="https://github.com/user-attachments/assets/0168e057-7589-4407-ab6b-e0e5d44f784c" />

#### 5. إدارة الواجبات والمهام الأكاديمية (Tasks & Assignments)[cite: 1]
<img width="1280" height="591" alt="Tasks-assignments" src="https://github.com/user-attachments/assets/4a557e2f-68d0-40f4-bf3c-b3a30d551cc2" />

#### 6. تقارير تحليل أداء الطالب (Performance Analysis)[cite: 1]
<img width="1280" height="589" alt="Performance" src="https://github.com/user-attachments/assets/49a18b56-b7ff-4364-b2dd-66dbe99eff3e" />

#### 7. ربط الحساب بولي الأمر (Add Guardian)[cite: 1]
<img width="1280" height="595" alt="Add guardian" src="https://github.com/user-attachments/assets/ed891a57-42c7-4fde-a82d-1022874955cb" />

#### 8. مشاركة الجدول والمهام مع الزملاء (Shared Tasks & Schedules)[cite: 1]
<img width="1280" height="565" alt="Shared" src="https://github.com/user-attachments/assets/998bd6a9-038f-4d3b-9327-5fc560b2fe64" />

#### 9. نظام التحفيز الملون والأنشطة (Gamification Feature)[cite: 1]
<img width="1280" height="592" alt="Gamification" src="https://github.com/user-attachments/assets/cda4886b-89ac-407b-b65d-e39d82139869" />

#### 10. تسجيل المقررات الجامعية المتاحة (Enroll in University Courses)[cite: 1]
<img width="1280" height="575" alt="Enroll in University Course" src="https://github.com/user-attachments/assets/3feaafdd-0c61-4075-840c-d41cbefb36c8" />

---

### 👨‍🏫 ثانياً: حساب عضو هيئة التدريس (Faculty Portal)

#### 1. الملف الشخصي للأستاذ (Faculty Profile)
<img width="1280" height="627" alt="Faculty profile" src="https://github.com/user-attachments/assets/d18d8986-49b5-48f6-ad24-c95c335202e0" />

#### 2. إدارة المقررات الأكاديمية (Faculty Courses Management)[cite: 1]
<img width="1280" height="612" alt="Faculty course" src="https://github.com/user-attachments/assets/f9d2954b-5bba-4332-bf38-6b7e5b8a7190" />

#### 3. بناء وإعداد الجداول الدراسية (Faculty Schedules Builder)[cite: 1]
<img width="1280" height="586" alt="Faculty schedules" src="https://github.com/user-attachments/assets/f7469bda-7759-46e3-8fd5-39a2df3f3bc0" />

#### 4. إسناد وإضافة الواجبات والمهام للطلاب (Add Tasks & Assignments)[cite: 1]
<img width="1280" height="570" alt="faculty-add tasks  assignment" src="https://github.com/user-attachments/assets/8fbe57fe-f2c6-4958-ae27-ca13ae5e12ba" />

---

### 👨‍👩‍👦 ثالثاً: حساب ولي الأمر (Parent Portal)

#### 1. الملف الشخصي لولي الأمر (Parent Profile)
<img width="1280" height="605" alt="Parent profile" src="https://github.com/user-attachments/assets/b7c1f6e3-be10-43bf-8372-4cbe366094b5" />

#### 2. إدارة وقبول دعوات ربط الأبناء (Invitations Management)[cite: 1]
<img width="1280" height="562" alt="Invitations" src="https://github.com/user-attachments/assets/438088d8-1460-4413-8728-8ea058d554e2" />

#### 3. استعراض قائمة الأبناء المرتبطين بالنظام (All Linked Students)[cite: 1]
<img width="1280" height="595" alt="All linked Students" src="https://github.com/user-attachments/assets/99314e00-364f-4693-b7bd-6218cdf010d2" />

#### 4. التقارير والإحصائيات البيانية لتقدم الأبناء (Reports & Statistics)[cite: 1]
<img width="1280" height="607" alt="Reports   Statistics" src="https://github.com/user-attachments/assets/5a3252b7-1963-4539-a19b-d1215c75531f" />

#### 5. متابعة تفاصيل أداء الطالب والمهام (Track Student Performance)[cite: 1]
<img width="1280" height="586" alt="Track Student Performance" src="https://github.com/user-attachments/assets/275500ba-653d-4b48-bd14-653bb4b24ef5" />



## Technologies

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap
- XAMPP


## Installation

1. Clone the repository
2. Import the database.sql file
3. Configure connection.php
4. Start Apache and MySQL in XAMPP
5. Open the project in localhost




## 📂 هيكل مجلدات المشروع (Folder Structure)
```text
academic-organizer/
├── assets/             # ملفات التنسيق والصور والأيقونات (CSS, Images, JS)
├── config/             # ملفات الاتصال بقاعدة البيانات والإعدادات العامة
├── includes/           # الأجزاء البرمجية المشتركة (Header, Footer, Navbar)
├── views/              # واجهات مستخدم النظام مقسمة حسب الأدوار (Student, Faculty, Parent, Admin)
├── database/           # ملف نسخة قاعدة البيانات SQL الاحتياطية
└── index.php           #الصفحة الرئيسية للمنصة
