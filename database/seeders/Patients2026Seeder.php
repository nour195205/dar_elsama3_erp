<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\Patient;
use App\Models\TestType;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Patients2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/patients_2026.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $this->command->info("Starting the Patient & Visit Import from: {$csvPath}");

        // 1. Caching existing Doctors to avoid N+1 queries
        $doctors = Doctor::all();
        $doctorsMap = [];
        foreach ($doctors as $doc) {
            $normalizedName = $this->normalizeName($doc->name);
            $doctorsMap[$normalizedName] = $doc;
        }

        // 2. Caching existing Test Types
        $testTypes = TestType::all();
        $testTypesMap = [];
        foreach ($testTypes as $tt) {
            $normalizedName = $this->normalizeName($tt->name);
            $testTypesMap[$normalizedName] = $tt;
        }

        $file = fopen($csvPath, 'r');
        if (!$file) {
            $this->command->error("Failed to open the CSV file.");
            return;
        }

        // Read and skip the header
        $headers = fgetcsv($file);

        $rowCount = 0;
        $insertedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // Skip completely empty rows or rows without proper structure
                if (empty($row) || count($row) < 5 || !isset($row[1])) {
                    continue;
                }

                // Convert encoding from Windows-1256 to UTF-8
                $row = array_map(function ($val) {
                    return iconv('windows-1256', 'utf-8//IGNORE', trim($val));
                }, $row);

                $rowCount++;

                // Columns mapping:
                // 0: * (ID/Index)
                // 1: التاريخ (Date)
                // 2: د.المركز (Center/Internal Doctor)
                // 3: اسم الحال (Patient Name)
                // 4: السن (Age)
                // 5: الموبايل (Phone)
                // 6: د. الخارجي (External Doctor)
                // 7: نوع الاشعه (Test Type)
                // 8: السعر (Price)
                // 9: نسبه د. المركز (Center Doctor Commission)
                // 10: نسبه د. خارجي (External Doctor Commission)
                // 11: المستلزمات (Supplies Cost)

                $dateStr = $row[1] ?? '';
                $internalDocName = $row[2] ?? '';
                $patientNameRaw = $row[3] ?? '';
                $ageRaw = $row[4] ?? '';
                $phone = $row[5] ?? '';
                $externalDocName = $row[6] ?? '';
                $testTypeNameRaw = $row[7] ?? '';
                $priceRaw = $row[8] ?? '0';
                $internalCommissionCsv = $row[9] ?? '0';
                $externalCommissionCsv = $row[10] ?? '0';
                $suppliesCostRaw = $row[11] ?? '0';

                // Skip rows without patient name
                if ($patientNameRaw === '' || $patientNameRaw === '0') {
                    continue;
                }

                // Parse Visit Date
                $visitDate = $this->parseDate($dateStr);
                $visitDateTime = $visitDate . ' 10:00:00';

                // Clean Patient Name
                $patientName = $this->cleanPatientName($patientNameRaw);

                // Parse numerical values
                $testPrice = (float) str_replace(',', '', $priceRaw);
                $suppliesCost = (float) str_replace(',', '', $suppliesCostRaw);
                $age = $this->parseAge($ageRaw);

                // Match or Create Internal Doctor
                $internalDoctor = null;
                if ($internalDocName !== '' && $internalDocName !== '0') {
                    $normIntName = $this->normalizeName($internalDocName);
                    if (isset($doctorsMap[$normIntName])) {
                        $internalDoctor = $doctorsMap[$normIntName];
                    } else {
                        // Determine commission details from CSV or defaults
                        $commValue = (float) str_replace(',', '', $internalCommissionCsv);
                        $internalDoctor = Doctor::create([
                            'name' => trim($internalDocName),
                            'type' => 'Internal',
                            'commission_type' => 'Flat',
                            'commission_value' => $commValue > 0 ? $commValue : 150.00,
                        ]);
                        $doctorsMap[$normIntName] = $internalDoctor;
                        $this->command->warn("Created Internal Doctor: {$internalDocName}");
                    }
                }

                // Match or Create External Doctor
                $externalDoctor = null;
                if ($externalDocName !== '' && $externalDocName !== '0') {
                    $normExtName = $this->normalizeName($externalDocName);
                    if (isset($doctorsMap[$normExtName])) {
                        $externalDoctor = $doctorsMap[$normExtName];
                    } else {
                        $commValue = (float) str_replace(',', '', $externalCommissionCsv);
                        $externalDoctor = Doctor::create([
                            'name' => trim($externalDocName),
                            'type' => 'External',
                            'commission_type' => 'Flat',
                            'commission_value' => $commValue > 0 ? $commValue : 50.00,
                        ]);
                        $doctorsMap[$normExtName] = $externalDoctor;
                        $this->command->warn("Created External Doctor: {$externalDocName}");
                    }
                }

                // Match or Create Test Type
                $testType = null;
                $testTypeId = null;
                if ($testTypeNameRaw !== '' && $testTypeNameRaw !== '0') {
                    $normTestName = $this->normalizeName($testTypeNameRaw);
                    if (isset($testTypesMap[$normTestName])) {
                        $testType = $testTypesMap[$normTestName];
                        $testTypeId = $testType->id;
                    } else {
                        $testType = TestType::create([
                            'name' => trim($testTypeNameRaw),
                            'price' => $testPrice,
                        ]);
                        $testTypesMap[$normTestName] = $testType;
                        $testTypeId = $testType->id;
                        $this->command->warn("Created Test Type: {$testTypeNameRaw}");
                    }
                }

                // Calculate Doctor Commissions using database settings (or CSV fallbacks if 0)
                $commInternal = 0.0;
                if ($internalDoctor) {
                    $commInternal = $this->calculateCommission($internalDoctor, $testPrice);
                    if ($commInternal == 0 && (float)$internalCommissionCsv > 0) {
                        $commInternal = (float) str_replace(',', '', $internalCommissionCsv);
                    }
                }

                $commExternal = 0.0;
                if ($externalDoctor) {
                    $commExternal = $this->calculateCommission($externalDoctor, $testPrice);
                    if ($commExternal == 0 && (float)$externalCommissionCsv > 0) {
                        $commExternal = (float) str_replace(',', '', $externalCommissionCsv);
                    }
                }

                // Visit Type logic
                $visitType = 'Initial';
                if ($testType && (str_contains($testType->name, 'استشارة') || str_contains($testType->name, 'إعادة') || str_contains($testType->name, 'اعادة'))) {
                    $visitType = 'Follow-up';
                }

                // 1. Create Patient with custom timestamps
                $patient = new Patient();
                $patient->name = $patientName;
                $patient->age = $age;
                $patient->phone = $phone;
                $patient->address = null;
                $patient->visit_type = $visitType;
                $patient->date = $visitDate;
                $patient->referring_doctor_id = $externalDoctor ? $externalDoctor->id : null;
                $patient->internal_doctor_id = $internalDoctor ? $internalDoctor->id : null;
                $patient->test_type_id = $testTypeId;
                $patient->test_price = $testPrice;
                $patient->supplies_cost = $suppliesCost;
                $patient->commission_internal = $commInternal;
                $patient->commission_external = $commExternal;
                
                $patient->created_at = $visitDateTime;
                $patient->updated_at = $visitDateTime;
                $patient->save();

                // 2. Create Transaction for patient exam revenue
                if ($testPrice > 0) {
                    $txRevenue = new Transaction();
                    $txRevenue->type = 'income';
                    $txRevenue->category = 'test_revenue';
                    $txRevenue->amount = $testPrice;
                    $txRevenue->description = "إيراد فحص: {$patientName} - " . ($testType ? $testType->name : '');
                    $txRevenue->reference_id = $patient->id;
                    $txRevenue->reference_type = Patient::class;
                    $txRevenue->date = $visitDate;
                    
                    $txRevenue->created_at = $visitDateTime;
                    $txRevenue->updated_at = $visitDateTime;
                    $txRevenue->save();
                }

                // 3. Create Internal Doctor Payout & Transaction
                if ($internalDoctor && $commInternal > 0) {
                    $payoutInt = new DoctorPayout();
                    $payoutInt->doctor_id = $internalDoctor->id;
                    $payoutInt->doctor_type = 'internal';
                    $payoutInt->patient_id = $patient->id;
                    $payoutInt->amount = $commInternal;
                    $payoutInt->calculation_basis = $internalDoctor->commission_type;
                    $payoutInt->calculation_value = $internalDoctor->commission_value;
                    $payoutInt->date = $visitDate;
                    $payoutInt->is_paid = false;
                    
                    $payoutInt->created_at = $visitDateTime;
                    $payoutInt->updated_at = $visitDateTime;
                    $payoutInt->save();

                    $txCommInt = new Transaction();
                    $txCommInt->type = 'payout';
                    $txCommInt->category = 'doctor_commission';
                    $txCommInt->amount = $commInternal;
                    $txCommInt->description = "عمولة طبيب داخلي ({$internalDoctor->name}) - مريض: {$patientName}";
                    $txCommInt->reference_id = $patient->id;
                    $txCommInt->reference_type = Patient::class;
                    $txCommInt->date = $visitDate;
                    
                    $txCommInt->created_at = $visitDateTime;
                    $txCommInt->updated_at = $visitDateTime;
                    $txCommInt->save();
                }

                // 4. Create External Doctor Payout & Transaction
                if ($externalDoctor && $commExternal > 0) {
                    $payoutExt = new DoctorPayout();
                    $payoutExt->doctor_id = $externalDoctor->id;
                    $payoutExt->doctor_type = 'external';
                    $payoutExt->patient_id = $patient->id;
                    $payoutExt->amount = $commExternal;
                    $payoutExt->calculation_basis = $externalDoctor->commission_type;
                    $payoutExt->calculation_value = $externalDoctor->commission_value;
                    $payoutExt->date = $visitDate;
                    $payoutExt->is_paid = false;
                    
                    $payoutExt->created_at = $visitDateTime;
                    $payoutExt->updated_at = $visitDateTime;
                    $payoutExt->save();

                    $txCommExt = new Transaction();
                    $txCommExt->type = 'payout';
                    $txCommExt->category = 'doctor_commission';
                    $txCommExt->amount = $commExternal;
                    $txCommExt->description = "عمولة طبيب خارجي ({$externalDoctor->name}) - مريض: {$patientName}";
                    $txCommExt->reference_id = $patient->id;
                    $txCommExt->reference_type = Patient::class;
                    $txCommExt->date = $visitDate;
                    
                    $txCommExt->created_at = $visitDateTime;
                    $txCommExt->updated_at = $visitDateTime;
                    $txCommExt->save();
                }

                // 5. Create supplies expense transaction if any
                if ($suppliesCost > 0) {
                    $txSupplies = new Transaction();
                    $txSupplies->type = 'expense';
                    $txSupplies->category = 'medical_supplies';
                    $txSupplies->amount = $suppliesCost;
                    $txSupplies->description = "مستلزمات طبية: {$patientName}";
                    $txSupplies->reference_id = $patient->id;
                    $txSupplies->reference_type = Patient::class;
                    $txSupplies->date = $visitDate;
                    
                    $txSupplies->created_at = $visitDateTime;
                    $txSupplies->updated_at = $visitDateTime;
                    $txSupplies->save();
                }

                $insertedCount++;
            }
            fclose($file);
            DB::commit();
            $this->command->info("Successfully imported {$insertedCount} records out of {$rowCount} CSV rows!");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            $this->command->error("An error occurred during import: " . $e->getMessage());
            Log::error("CSV Seeder Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * حساب عمولة الطبيب بناءً على نوع القيمة (نسبة مئوية أو مبلغ ثابت)
     */
    private function calculateCommission(Doctor $doctor, float $testPrice): float
    {
        $valStr = (string) $doctor->commission_value;
        $commissionType = $doctor->commission_type;

        if (str_contains($valStr, '%') || $commissionType === 'Percentage') {
            $numericVal = (float) str_replace('%', '', $valStr);
            return $testPrice * ($numericVal / 100);
        }

        return (float) $doctor->commission_value;
    }

    /**
     * تنظيف وتوحيد أسماء المرضى إملائياً
     */
    private function cleanPatientName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/u', ' ', $name);

        // توحيد الهمزات
        $name = preg_replace('/[أإآ]/u', 'ا', $name);

        // توحيد الياء والألف المقصورة
        $name = preg_replace('/ى\b/u', 'ي', $name);
        $name = str_replace('ى', 'ي', $name);

        // توحيد التاء المربوطة والهاء
        $name = preg_replace('/ة\b/u', 'ه', $name);

        // معالجة التكرار للأحرف (مثل داووود)
        $name = preg_replace('/(و)\1+/u', '$1', $name);
        $name = preg_replace('/(ا)\1+/u', '$1', $name);
        $name = preg_replace('/(ي)\1+/u', '$1', $name);
        $name = preg_replace('/(د)\1+/u', '$1', $name);

        return $name;
    }

    /**
     * توحيد أسماء الأطباء والفحوصات للمقارنة والمطابقة
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');

        // إزالة الألقاب الطبية الشائعة
        $name = preg_replace('/^د[\.\/\\\ ]+/u', '', $name);

        // توحيد الهمزات والتاء المربوطة والألف المقصورة
        $name = preg_replace('/[أإآ]/u', 'ا', $name);
        $name = preg_replace('/ة/u', 'ه', $name);
        $name = preg_replace('/ى/u', 'ي', $name);

        // معالجة التكرار
        $name = preg_replace('/(و)\1+/u', '$1', $name);
        $name = preg_replace('/(ا)\1+/u', '$1', $name);
        $name = preg_replace('/(د)\1+/u', '$1', $name);

        // إزالة جميع المسافات والرموز لتجنب أخطاء الفراغات
        $name = preg_replace('/[^\p{L}\p{N}]/u', '', $name);

        return $name;
    }

    /**
     * تحويل صيغة التاريخ من M/D/YYYY إلى YYYY-MM-DD
     */
    private function parseDate(string $dateStr): string
    {
        $parts = explode('/', $dateStr);
        if (count($parts) === 3) {
            $month = (int) $parts[0];
            $day = (int) $parts[1];
            $year = (int) $parts[2];
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * تحليل العمر بأمان
     */
    private function parseAge(string $ageRaw): int
    {
        if ($ageRaw === '' || $ageRaw === '0') {
            return 0;
        }

        $ageFloat = (float) $ageRaw;
        // إذا كان العمر كسر وطفل (مثل 1.8 سنة) نقوم بتقريبه لأقرب رقم صحيح
        return (int) round($ageFloat);
    }
}
