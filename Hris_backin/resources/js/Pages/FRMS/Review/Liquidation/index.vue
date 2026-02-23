<template>
    <FRMSLayout>
        <SectionTitleLineWithButton :icon="'mdiReceiptText'" title="Liquidation Report" main>
            <BaseButtons>
                <BaseButton
                    @click="exportToExcel"
                    :icon="'mdiFileExcel'"
                    label="Export Excel"
                    color="success"
                    :disabled="liquidationData.length === 0"
                    rounded-full
                    small
                />
                <BaseButton
                    @click="exportToPDF"
                    :icon="'mdiFilePdf'"
                    label="Export PDF"
                    color="danger"
                    :disabled="liquidationData.length === 0"
                    rounded-full
                    small
                />
            </BaseButtons>
        </SectionTitleLineWithButton>

        <CardBox class="mb-6">
            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <FormField label="Date From">
                    <FormControl
                        type="date"
                        v-model="filters.date_from"
                        @change="applyFilters"
                    />
                </FormField>
                <FormField label="Date To">
                    <FormControl
                        type="date"
                        v-model="filters.date_to"
                        @change="applyFilters"
                    />
                </FormField>
                <FormField label="Site">
                    <FormControl
                        v-model="filters.site_id"
                        :options="siteOptions"
                        @change="applyFilters"
                    />
                </FormField>
                <FormField label="Status">
                    <FormControl
                        v-model="filters.status"
                        :options="statusOptions"
                        @change="applyFilters"
                    />
                </FormField>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-purple-500 text-white p-4 rounded-lg">
                    <h5 class="text-sm font-medium opacity-90">Total Expenses</h5>
                    <h3 class="text-2xl font-bold">₱{{ formatCurrency(summary.total_expenses) }}</h3>
                </div>
                <div class="bg-blue-500 text-white p-4 rounded-lg">
                    <h5 class="text-sm font-medium opacity-90">Total Records</h5>
                    <h3 class="text-2xl font-bold">{{ summary.total_records }}</h3>
                </div>
                <div class="bg-green-500 text-white p-4 rounded-lg">
                    <h5 class="text-sm font-medium opacity-90">Average Expense</h5>
                    <h3 class="text-2xl font-bold">₱{{ formatCurrency(averageExpense) }}</h3>
                </div>
            </div>
        </CardBox>

        <CardBox class="flex-1" has-table>
            <CoreTable
                v-if="liquidationData.length > 0"
                :table-rows="liquidationData"
                :table-header="tableHeader"
                table-name="Liquidation Report"
                :searchable-fields="['ref_num', 'first_name', 'last_name', 'branch_name', 'description']"
                :is-paginated="false"
                @openLink="openLink"
            >
                <template #table-action>
                    <BaseButtons>
                        <BaseButton :icon="'mdiRefresh'" title="Refresh Data" color="whiteDark" @click="refreshData" />
                    </BaseButtons>
                </template>

                <template #status="{ row }">
                    <span
                        :class="getStatusClass(row.status)"
                        class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                        {{ getStatusText(row.status) }}
                    </span>
                </template>
                
                <template #expense_amount="{ row }">
                    <span class="font-bold text-purple-600">
                        ₱{{ formatCurrency(row.expense_amount) }}
                    </span>
                </template>
                
                <template #employee="{ row }">
                    <div>
                        <strong>{{ row.first_name }} {{ row.last_name }}</strong>
                    </div>
                </template>
                
                <template #date="{ row }">
                    {{ formatDate(row.date) }}
                </template>
            </CoreTable>
            <div v-else class="text-gray-500 text-center py-8">
                No liquidation records found. Adjust your filters to view data.
            </div>
        </CardBox>

        <AsideDrawer title="Liquidation Details" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-purple-500/50">
            <FormField label="Reference No.">
                <FormControl v-model="selectedRecord.ref_num" :disabled="true" />
            </FormField>
            <FormField label="Employee">
                <FormControl :model-value="`${selectedRecord.first_name} ${selectedRecord.last_name}`" :disabled="true" />
            </FormField>
            <FormField label="Site">
                <FormControl v-model="selectedRecord.branch_name" :disabled="true" />
            </FormField>
            <FormField label="Expense Amount">
                <FormControl :model-value="`₱${formatCurrency(selectedRecord.expense_amount)}`" :disabled="true" />
            </FormField>
            <FormField label="Date">
                <FormControl :model-value="formatDate(selectedRecord.date)" :disabled="true" />
            </FormField>
            <FormField label="Status">
                <FormControl :model-value="getStatusText(selectedRecord.status)" :disabled="true" />
            </FormField>
            <FormField label="Description">
                <FormControl v-model="selectedRecord.description" :disabled="true" />
            </FormField>

            <BaseButtons class="mt-4">
                <BaseButton 
                    v-if="selectedRecord.status === 'P'" 
                    color="info" 
                    @click="viewDetails" 
                    label="View Full Details" 
                    icon="mdiEye" 
                />
                <BaseButton 
                    color="contrast" 
                    @click="exportSingleRecord" 
                    label="Export Record" 
                    icon="mdiDownload" 
                />
            </BaseButtons>
        </AsideDrawer>
    </FRMSLayout>
</template>

<script>
import FRMSLayout from '../../../../Layouts/FRMSLayout.vue';
import CoreTable from '../../../../Components/CoreTable.vue';
import SectionTitleLineWithButton from '../../../../Components/SectionTitleLineWithButton.vue';
import CardBox from '../../../../Components/CardBox.vue';
import FormField from '../../../../Components/FormField.vue';
import FormControl from '../../../../Components/FormControl.vue';
import BaseButton from '../../../../Components/BaseButton.vue';
import BaseButtons from '../../../../Components/BaseButtons.vue';
import AsideDrawer from '../../../../Components/AsideDrawer.vue';
import { router } from '@inertiajs/vue3';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

export default {
    name: 'LiquidationReport',
    components: {
        FRMSLayout,
        CoreTable,
        SectionTitleLineWithButton,
        CardBox,
        FormField,
        FormControl,
        BaseButton,
        BaseButtons,
        AsideDrawer,
    },
    props: {
        liquidationData: {
            type: Array,
            default: () => []
        },
        filters: {
            type: Object,
            default: () => ({})
        },
        summary: {
            type: Object,
            default: () => ({})
        },
        sites: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            filters: {
                date_from: this.filters?.date_from || '',
                date_to: this.filters?.date_to || '',
                site_id: this.filters?.site_id || '',
                status: this.filters?.status || '',
            },
            tableHeader: [
                { key: 'ref_num', label: 'Reference No.', sortable: true },
                { key: 'employee', label: 'Employee', sortable: true },
                { key: 'branch_name', label: 'Site', sortable: true },
                { key: 'expense_amount', label: 'Expense Amount', sortable: true },
                { key: 'date', label: 'Date', sortable: true },
                { key: 'status', label: 'Status', sortable: true },
                { key: 'description', label: 'Description', sortable: false }
            ],
            openDrawer: false,
            selectedRecord: {}
        };
    },
    computed: {
        averageExpense() {
            if (this.summary.total_records === 0) return 0;
            return this.summary.total_expenses / this.summary.total_records;
        },
        siteOptions() {
            return [
                { value: '', label: 'All Sites' },
                ...this.sites.map(site => ({
                    value: site.id,
                    label: site.branch_name
                }))
            ];
        },
        statusOptions() {
            return [
                { value: '', label: 'All Status' },
                { value: 'P', label: 'Pending' },
                { value: 'A', label: 'Approved' },
                { value: 'R', label: 'Rejected' }
            ];
        }
    },
    methods: {
        applyFilters() {
            router.get(route('frms.reports.liquidation'), this.filters, {
                preserveState: true,
                preserveScroll: true,
            });
        },
        formatCurrency(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        },
        getStatusClass(status) {
            const statusClasses = {
                'P': 'bg-yellow-100 text-yellow-800',
                'A': 'bg-green-100 text-green-800',
                'R': 'bg-red-100 text-red-800',
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        getStatusText(status) {
            const statusTexts = {
                'P': 'Pending',
                'A': 'Approved',
                'R': 'Rejected',
            };
            return statusTexts[status] || 'Unknown';
        },
        openLink(row) {
            this.selectedRecord = row;
            this.openDrawer = true;
        },
        viewDetails() {
            // Navigate to liquidation details
            console.log('View full details for:', this.selectedRecord);
        },
        refreshData() {
            router.get(route('frms.reports.liquidation'), this.filters, {
                preserveState: true,
                preserveScroll: true,
            });
        },
        exportSingleRecord() {
            // Export single record functionality
            console.log('Export single record:', this.selectedRecord);
        },
        exportToExcel() {
            // Create CSV content
            const headers = ['Reference No.', 'Employee', 'Site', 'Expense Amount', 'Date', 'Status', 'Description'];
            const csvContent = [
                headers.join(','),
                ...this.liquidationData.map(row => [
                    row.ref_num || '',
                    `"${row.first_name} ${row.last_name}"`,
                    `"${row.branch_name || ''}"`,
                    row.expense_amount || 0,
                    this.formatDate(row.date),
                    this.getStatusText(row.status),
                    `"${row.description || 'N/A'}"`
                ].join(','))
            ].join('\n');

            // Download file
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `liquidation_report_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        },
        exportToPDF() {
            const doc = new jsPDF();
            
            // Company Header
            doc.setFontSize(20);
            doc.setFont('helvetica', 'bold');
            doc.text('SafExpress Finance System', 105, 20, { align: 'center' });
            
            doc.setFontSize(16);
            doc.setFont('helvetica', 'normal');
            doc.text('Liquidation Report', 105, 30, { align: 'center' });
            
            // Report Date
            doc.setFontSize(10);
            doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 20, 45);
            
            // Filters Information
            let yPos = 55;
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.text('Report Filters:', 20, yPos);
            
            yPos += 10;
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            
            if (this.filters.date_from || this.filters.date_to) {
                const dateRange = `${this.filters.date_from || 'Start'} to ${this.filters.date_to || 'End'}`;
                doc.text(`Date Range: ${dateRange}`, 20, yPos);
                yPos += 8;
            }
            
            if (this.filters.site_id) {
                const selectedSite = this.sites.find(site => site.id == this.filters.site_id);
                doc.text(`Site: ${selectedSite ? selectedSite.branch_name : 'All Sites'}`, 20, yPos);
                yPos += 8;
            }
            
            if (this.filters.status) {
                doc.text(`Status: ${this.getStatusText(this.filters.status)}`, 20, yPos);
                yPos += 8;
            }
            
            // Summary Information
            yPos += 5;
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.text('Summary:', 20, yPos);
            
            yPos += 10;
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text(`Total Expenses: ₱${this.formatCurrency(this.summary.total_expenses)}`, 20, yPos);
            yPos += 8;
            doc.text(`Total Records: ${this.summary.total_records}`, 20, yPos);
            yPos += 8;
            doc.text(`Average Expense: ₱${this.formatCurrency(this.averageExpense)}`, 20, yPos);
            
            // Table Data
            const tableData = this.liquidationData.map(row => [
                row.ref_num || '',
                `${row.first_name} ${row.last_name}`,
                row.branch_name || '',
                `₱${this.formatCurrency(row.expense_amount)}`,
                this.formatDate(row.date),
                this.getStatusText(row.status),
                row.description || 'N/A'
            ]);
            
            doc.autoTable({
                head: [['Reference No.', 'Employee', 'Site', 'Expense Amount', 'Date', 'Status', 'Description']],
                body: tableData,
                startY: yPos + 15,
                styles: {
                    fontSize: 8,
                    cellPadding: 3,
                },
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                columnStyles: {
                    0: { cellWidth: 25 }, // Reference No.
                    1: { cellWidth: 30 }, // Employee
                    2: { cellWidth: 25 }, // Site
                    3: { cellWidth: 25 }, // Expense Amount
                    4: { cellWidth: 20 }, // Date
                    5: { cellWidth: 20 }, // Status
                    6: { cellWidth: 35 }  // Description
                }
            });
            
            // Footer
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.text(`Page ${i} of ${pageCount}`, 105, doc.internal.pageSize.height - 10, { align: 'center' });
                doc.text('SafExpress Finance System - Liquidation Report', 20, doc.internal.pageSize.height - 10);
            }
            
            // Save the PDF
            doc.save(`liquidation_report_${new Date().toISOString().split('T')[0]}.pdf`);
        }
    }
};
</script>

<style scoped>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
