<template>
        <SectionTitleLineWithButton :icon="'mdiReceiptText'" title="Liquidation Report" main/>
        <CardBox class="mb-6">
            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 items-end">
                <FormField label="Warehouse">
                    <FormControl
                        v-model="filters.site_id"
                        :options="siteOptions"
                        @update:modelValue="onSiteChange"
                    />
                </FormField>
                <div class="flex md:justify-start">
                    <BaseButton
                        color="contrast"
                        :icon="'mdiCloudDownloadOutline'"
                        label="Get Report Data"
                        class="self-end"
                        @click="applyFilters"
                    />
                </div>
            </div>

<CoreTable
                v-if="loading || tableRows.length > 0"
                :table-rows="tableRows"
                :table-header="tableHeader"
                table-name="Liquidation Report"
                :searchable-fields="'ref_num,employee,branch_name,description'"
                :is-paginated="false"
                :loading="loading"
                @openLink="openLink"
            >


                <template #row-action="{ slotProp }">
                    <BaseButton color="info" :icon="'mdiEye'" label="View" @click="showDetails(slotProp)" />
                </template>
            </CoreTable>
            <div v-else class="text-gray-500 text-center py-8">
                No liquidation records found. Adjust your filters to view data.
            </div>
        </CardBox>



    </template>

<script>
import CoreTable from '../../../../Components/CoreTable.vue';
import SectionTitleLineWithButton from '../../../../Components/SectionTitleLineWithButton.vue';
import CardBox from '../../../../Components/CardBox.vue';
import FormField from '../../../../Components/FormField.vue';
import FormControl from '../../../../Components/FormControl.vue';
import BaseButton from '../../../../Components/BaseButton.vue';
import BaseButtons from '../../../../Components/BaseButtons.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

export default {
    name: 'LiquidationReport',
    components: {
        CoreTable,
        SectionTitleLineWithButton,
        CardBox,
        FormField,
        FormControl,
        BaseButton,
        BaseButtons,
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
                site_id: this.filters?.site_id || '',
            },
            loading: false,
            localRows: [],
            tableHeader: [
                { fieldName: 'ref_num', label: 'Reference No.', sortable: true },
                { fieldName: 'employee', label: 'Employee', sortable: true },
                { fieldName: 'branch_name', label: 'Site', sortable: true },
                { fieldName: 'expense_amount', label: 'Expense Amount', sortable: true },
                { fieldName: 'date', label: 'Date', sortable: true, type: 'date' },
                { fieldName: 'status', label: 'Status', sortable: true },
                { fieldName: 'description', label: 'Description', sortable: false },
                { fieldName: 'view', label: 'View', sortable: false, type: 'slot' },
            ],
            selectedRecord: {},
        };
    },
    computed: {
        averageExpense() {
            if (this.summary.total_records === 0) return 0;
            return this.summary.total_expenses / this.summary.total_records;
        },
        tableRows() {
            const source = this.localRows.length > 0 ? this.localRows : this.liquidationData;
            if (!this.filters.site_id) return source;
            const selectedId = this.filters.site_id;
            return source.filter(r => r.branch_id == selectedId);
        },
        siteOptions() {
            return [
                { id: '', label: 'All Sites' },
                ...this.sites.map(site => ({
                    id: site.id,
                    label: site.branch_name
                }))
            ];
        }
    },


    methods: {
        onSiteChange(val) {
            this.filters.site_id = val
        },
        applyFilters() {
            this.loading = true
            let url = route('frls.reports.liquidation')
            axios.get(url, { params: { site_id: this.filters.site_id } })
                .then(res => {
                    if (res.data && res.data.success) {
                        this.localRows = res.data.liquidationData ?? []
                    }
                })
                .catch(() => {
                    this.localRows = []
                })
                .finally(() => {
                    this.loading = false
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
                // Disbursement statuses
                'P': 'bg-yellow-100 text-yellow-800',
                'O': 'bg-blue-100 text-blue-800',
                'A': 'bg-green-100 text-green-800',
                'C': 'bg-gray-100 text-gray-800',
                'X': 'bg-red-100 text-red-800',
                'R': 'bg-red-100 text-red-800',
                // Form request statuses
                'FA': 'bg-yellow-100 text-yellow-800',
                'FD': 'bg-indigo-100 text-indigo-800',
                'FL': 'bg-purple-100 text-purple-800',
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        getStatusText(status) {
            const statusTexts = {
                // Disbursement statuses
                'P': 'Pending',
                'O': 'Open',
                'A': 'Approved',
                'C': 'Closed',
                'X': 'Canceled',
                'R': 'Rejected',
                // Form request statuses
                'FA': 'For Approval',
                'FD': 'For Disbursement',
                'FL': 'For Liquidation',
            };
            return statusTexts[status] || 'Unknown';
        },
        openLink(row) {
            this.selectedRecord = row;
        },
        showDetails(row) {
            this.$emit('triggerTopRightButton', 'Details', row);
        },
        viewDetails() {
            // Navigate to liquidation details
            console.log('View full details for:', this.selectedRecord);
        },
        refreshData() {
            router.get(route('frls.reports.liquidation'), this.filters, {
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
                    `"${row.employee || ''}"`,
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

            if (this.filters.site_id) {
                const selectedSite = this.sites.find(site => site.id == this.filters.site_id);
                doc.text(`Site: ${selectedSite ? selectedSite.branch_name : 'All Sites'}`, 20, yPos);
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
}
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
