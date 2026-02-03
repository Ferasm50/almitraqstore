# نظام إدارة الحوالات وتحديث أرصدة البائعين

## نظرة عامة

هذا النظام يدير عملية الموافقة على حوالات العملاء وتحديث أرصدة البائعين تلقائياً عند بيع منتجاتهم.

## المكونات الرئيسية

### 1. الموديلات (Models)

#### CustomerTransfer
- يدير بيانات حوالات العملاء
- يحتوي على دالة `markAsApproved()` التي تقوم بـ:
  - تحديث حالة الحوالة إلى "approved"
  - تحديث حالة الطلب إلى "paid"
  - تحديث رصيد كل بائع بقيمة المنتجات المباعة
  - تسجيل العملية في سجل المعاملات

#### VendorTransaction
- يسجل جميع معاملات البائعين
- يدعم أنواع المعاملات:
  - **sale**: عملية بيع منتج
  - **withdrawal**: عملية سحب رصيد
  - **refund**: عملية استرجاع منتج
- يحتوي على دوال ثابتة لتسهيل تسجيل المعاملات:
  - `logSale()`: تسجيل عملية بيع
  - `logWithdrawal()`: تسجيل عملية سحب
  - `logRefund()`: تسجيل عملية استرجاع

#### StoreBalance
- يدير رصيد كل متجر
- يحتوي على:
  - `balance`: الرصيد الحالي
  - `total_sales`: إجمالي المبيعات
  - `total_withdrawn`: إجمالي المسحوبات
  - القيم الافتراضية: 0 لجميع الحقول

### 2. الكنترولر (Controllers)

#### TransferApprovalController
- `pendingTransfers()`: عرض قائمة الحوالات المعلقة
- `showTransfer($id)`: عرض تفاصيل حوالة معينة
- `approveTransfer($id)`: الموافقة على الحوالة وتحديث أرصدة البائعين
- `rejectTransfer($id)`: رفض الحوالة
- `vendorTransactions($storeId)`: عرض سجل معاملات بائع معين

### 3. العروض (Views)

#### admin/transfers/pending.blade.php
- عرض قائمة الحوالات المعلقة
- أزرار لعرض وقبول كل حوالة

#### admin/transfers/show.blade.php
- عرض تفاصيل حوالة معينة
- معلومات العميل والمتجر
- تفاصيل البنك والحوالة
- قائمة المنتجات في الطلب
- أزرار قبول/رفض الحوالة

#### admin/transfers/vendor-transactions.blade.php
- عرض سجل معاملات بائع معين
- إحصائيات الرصيد والمبيعات والمسحوبات
- جدول بجميع المعاملات مع التفاصيل

## سير العمل (Workflow)

### عند الموافقة على الحوالة:

1. يقوم الأدمن بالضغط على زر "قبول الحوالة"
2. يتم استدعاء `approveTransfer()` في الكنترولر
3. يتم تحديث حالة الحوالة إلى "approved"
4. يتم تحديث حالة الطلب إلى "paid"
5. يتم التكرار على جميع عناصر الطلب:
   - لكل عنصر، يتم تحديث رصيد البائع
   - يتم تسجيل العملية في سجل المعاملات
6. يتم عرض رسالة نجاح

### عند رفض الحوالة:

1. يقوم الأدمن بالضغط على زر "رفض الحوالة"
2. يظهر نافذة منبثقة لإدخال سبب الرفض
3. يتم استدعاء `rejectTransfer()` في الكنترولر
4. يتم تحديث حالة الحوالة إلى "rejected"
5. يتم حفظ سبب الرفض
6. يتم عرض رسالة نجاح

## قواعد البيانات

### جدول vendor_transactions
```sql
- id: المفتاح الأساسي
- store_id: معرف المتجر (مفتاح خارجي)
- order_id: معرف الطلب (مفتاح خارجي)
- order_item_id: معرف عنصر الطلب (مفتاح خارجي)
- transfer_id: معرف الحوالة (مفتاح خارجي)
- type: نوع المعاملة (sale/withdrawal/refund)
- amount: المبلغ
- balance_before: الرصيد قبل العملية
- balance_after: الرصيد بعد العملية
- description: وصف العملية
- status: حالة العملية (pending/completed/failed)
- created_at, updated_at: التواريخ
```

## المسارات (Routes)

يجب إضافة المسارات التالية إلى ملف routes/web.php:

```php
Route::prefix('admin')->name('admin.')->middleware(['auth', 'isAdmin'])->group(function () {
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/pending', [TransferApprovalController::class, 'pendingTransfers'])->name('pending');
        Route::get('/{id}', [TransferApprovalController::class, 'showTransfer'])->name('show');
        Route::post('/{id}/approve', [TransferApprovalController::class, 'approveTransfer'])->name('approve');
        Route::post('/{id}/reject', [TransferApprovalController::class, 'rejectTransfer'])->name('reject');
        Route::get('/vendor/{storeId}/transactions', [TransferApprovalController::class, 'vendorTransactions'])->name('vendor');
    });
});
```

## الميزات

✅ تحديث تلقائي لرصيد البائع عند بيع منتج
✅ تسجيل كامل لجميع المعاملات
✅ عرض تفصيلي لسجل المعاملات
✅ واجهة سهلة الاستخدام للأدمن
✅ دعم عمليات البيع والسحب والاسترجاع
✅ حماية من الأخطاء باستخدام المعاملات (Transactions)

## التثبيت

1. تشغيل الترحيل (Migration):
```bash
php artisan migrate
```

2. إضافة المسارات إلى ملف routes/web.php

3. الوصول إلى صفحة الحوالات المعلقة:
```
/admin/transfers/pending
```

## الصيانة

للتأكد من عمل النظام بشكل صحيح:
1. تحقق من أن جميع العلاقات بين الموديلات صحيحة
2. تأكد من أن قواعد البيانات تحتوي على جميع الجداول المطلوبة
3. اختبر عملية الموافقة على حوالة وتحقق من تحديث الرصيد
4. راجع سجل المعاملات للتأكد من تسجيل جميع العمليات
