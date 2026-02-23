<script setup lang="ts">
import { computed, provide, ref, watch } from "vue";
import { core } from "@/core";
import SortLink from "@/Components/SortLink.vue";
import TableCheckboxCell from "@/Components/TableCheckboxCell.vue";
import BaseLevel from "@/Components/BaseLevel.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import FormControl from "@/Components/FormControl.vue";
import { mdiFilterMultipleOutline } from "@mdi/js";
import FormFieldHorizontal from "@/Components/FormFieldHorizontal.vue";
import CorePaginate from "./CorePaginate.vue";

interface TableHeader {
  label: string;
  fieldName: string;
  columnRowStyle?: string;
  columnRowClass?: string;
  columnRowValueClass?: string;
  columnHeadStyle?: string;
  columnHeadClass?: string;
  type?: string;
  appendText?: string;
  options?: Record<string, string>;
  enumerable?: Record<string, string>;
}

interface TableRow {
  [key: string]: any;
}

interface Props {
  tableName: string;
  tableHeader: TableHeader[];
  tableRows: TableRow[];
  isDownloadable?: boolean;
  sortDefaultDir?: string;
  rowsPerPage?: number;
  sortDefaultColumn?: string;
  isPaginated?: boolean;
  isShowRowCheckbox?: boolean;
  isRowFocusSelect?: boolean;
  searchableFields?: string;
  tableClass?: string;
  rowsToDisplayOnOpen?: number;
  rowFocusSelectIdentifier?: string;
  filterFieldStatusLabel?: string;
  filterFieldStatusField?: string;
  showWhenVisible?: boolean;
  showTotal?: boolean;
  showPageInfo?: boolean;
  showDownloadCsv?: boolean;
  loading?: boolean;
}

const props = defineProps<Props>();

const searchedText = ref<string>("");
const currentPage = ref<number>(0);
const perPage = ref<number>(props.rowsPerPage ?? 10);
const tableRowsToDisplay = ref<TableRow[]>(props.tableRows);
let currentDir = ref<string>(props.sortDefaultDir ?? "asc");
let currentSort = ref<string | undefined>(props.sortDefaultColumn);
let isPaginated = ref<boolean>(props.isPaginated ?? false);
let pageSize = ref<number>(props.rowsPerPage ?? 10);

const showTotal = computed(() => props.showTotal ?? true);
const showPageInfo = computed(() => props.showPageInfo ?? true);
const showDownloadCsv = computed(() => props.showDownloadCsv ?? true);

// Computed
const currentPageText = computed(() => currentPage.value + 1);
const rowsOnOpen = ref<number>(
  props.rowsToDisplayOnOpen ?? (props.isPaginated ? perPage.value : tableRowsToDisplay.value.length)
);
const numberOfPages = computed(() => Math.ceil(tableRowsToDisplay.value.length / perPage.value));

// This computed values takes care of the records displayed on the table seen by user
const rowsInPage = computed(() => {
  if(props.showWhenVisible){
    return props.tableRows;
  }else{
    if (isPaginated.value && !props.rowsToDisplayOnOpen) return paginate(tableRowsToDisplay.value);
    return displayRowsByLimit();
  }

});

function displayRowsByLimit() {
  if (props.sortDefaultColumn && props.sortDefaultDir) {
    tableRowsToDisplay.value.sort((a, b) => {
      let modifier = 1;
      if (props.sortDefaultDir!.toLowerCase() === "desc") {
        modifier = -1;
      }
      if (a[props.sortDefaultColumn!] < b[props.sortDefaultColumn!]) {
        return -1 * modifier;
      }
      if (a[props.sortDefaultColumn!] > b[props.sortDefaultColumn!]) {
        return modifier;
      }
      return 0;
    });
  }
  return tableRowsToDisplay.value.slice(0, rowsOnOpen.value);
}

// ################# Group Code: Support for Pagination ####################################################
function paginate(data: TableRow[]) {
  return data
    ? data.slice(perPage.value * currentPage.value, perPage.value * (currentPage.value + 1))
    : [];
}
const paginateClick = (p: number) => {
  currentPage.value = p;
};

// ################# Group Code: Support for Sorting ####################################################
provide("getCurrentSort", getCurrentSort);
provide("getSortIcon", getSortIcon);
provide("sortBy", sortBy);

function getCurrentSort() {
  return currentSort.value;
}

function getSortIcon() {
  if (currentDir.value === "asc") {
    return "<small class='text-gray-400'> ▲</small>";
  } else {
    return "<small class='text-gray-400'> ▼</small>";
  }
}
function sortBy(s: string) {
  //if s == current sort, reverse
  if (s === currentSort.value) {
    currentDir.value = currentDir.value === "asc" ? "desc" : "asc";
  }
  currentSort.value = s;

  tableRowsToDisplay.value.sort((a, b) => {
    let modifier = 1;
    if (currentDir.value === "desc") {
      modifier = -1;
    }
    if (a[s] < b[s]) {
      return -1 * modifier;
    }
    if (a[s] > b[s]) {
      return modifier;
    }
    return 0;
  });
}

// ################# Table Functions ####################################################

// Download data : available when props isDownloadable is true
function downloadCSVData() {
  let csv = "";
  let rowIndex = 0;
  let row: string[] = [];
  let headIndex = 0;
  let fieldData = "";

  //the column headers
  props.tableHeader.forEach(function () {
    row.push(props.tableHeader[headIndex].label);
    headIndex++;
  });

  csv += row.join(",");
  csv += "\n";

  // the row data
  for (let i = 0; i < tableRowsToDisplay.value.length; i++) {
    row = [];
    headIndex = 0;
    for (let j = 0; j < props.tableHeader.length; j++) {
      fieldData = getRowFieldValue(
        tableRowsToDisplay.value[i],
        props.tableHeader[j].fieldName
      ).toString();
      fieldData = fieldData.replace(/,/g, " "); // we need to replace comma value with space to avoid cluttered rows
      row.push(fieldData);
      headIndex++;
    }
    csv += row.join(",");
    csv += "\n";
  }

  const anchor = document.createElement("a");
  anchor.href = "data:text/csv;charset=utf-8," + encodeURIComponent(csv);
  anchor.target = "_blank";
  anchor.download =
    (props.tableName ? props.tableName : document.title) + ".csv";
  anchor.click();
}

//get the row field value up to second level object
function getRowFieldValue(row: TableRow, fieldName: string, extraText = ""): string {
  let value = "";
  if (fieldName) {
    const childFieldArray = fieldName.split(".");
    if (childFieldArray.length > 1) {
      if (row[childFieldArray[0]])
        value = row[childFieldArray[0]][childFieldArray[1]];
    } else {
      value = row[fieldName];
    }
    return value || parseInt(value) === 0 ? value + extraText : "";
  }
  return extraText;
}

//get column passed styling from parent if there is
function getColumnRowStyle(field: TableHeader) {
  if (field.hasOwnProperty("columnRowStyle")) {
    return field.columnRowStyle;
  }
}

//get column passed styling from parent if there is
function getColumnRowClass(field: TableHeader) {
  if (field.hasOwnProperty("columnRowClass")) {
    return field.columnRowClass;
  }
}

//get column row value styling from parent if there is
function getColumnRowValueClass(field: TableHeader) {
  if (field.hasOwnProperty("columnRowValueClass")) {
    return field.columnRowValueClass;
  }
}

//get column passed styling from parent if there is
function getColumnHeadStyle(field: TableHeader) {
  if (field.hasOwnProperty("columnHeadStyle")) {
    return field.columnHeadStyle;
  }
}

//get column passed styling from parent if there is
function getColumnHeadClass(field: TableHeader) {
  if (field.hasOwnProperty("columnHeadClass")) {
    return field.columnHeadClass;
  }
}

//filter based on searchableText
function doSearch() {
  const searchableFields = props.searchableFields?.split(",") ?? [];

  const checkIfSearchableFieldsInRow = (row: TableRow) => {
    if (!(searchedText.value && searchedText.value.trim().length !== 0 && searchableFields.length > 0)) return true

    let found = false;
    for (let i = 0; i < searchableFields.length; i++) {
      let splitfields = searchableFields[i].split(".");

      if (!row.hasOwnProperty(splitfields[0])) continue;
      if (splitfields.length > 1 && !row[splitfields[0]].hasOwnProperty(splitfields[1])) continue;

      found = ((splitfields.length > 1 ? row[splitfields[0]][splitfields[1]] : row[searchableFields[i]]) ?? '')
        .toString()
        .toLowerCase()
        .includes(searchedText.value.toLowerCase());
      if (found) return found;
    }
    return found;
  }

  tableRowsToDisplay.value = props.tableRows.filter(checkIfSearchableFieldsInRow)
  currentPage.value = 0;
}

// Show all records
function showAllRecords() {
  rowsOnOpen.value = tableRowsToDisplay.value.length;
  isPaginated.value = false;
}
const emit = defineEmits(["openLink", "htmlClick", "rowClick", "checkRows"]);

//open hyperlink. we are expecting that the parent created a method that receives the event
function openLink(link: any) {
  emit("openLink", link);
}

const htmlClick = (event: Event) => {
  emit("htmlClick", (event.target as HTMLElement));
};

const rowClick = (rowFocusSelectIdentifier: string) => {
  emit("rowClick", rowFocusSelectIdentifier);
}

const checkedRows = ref<TableRow[]>([]);

const checkIfSelected = (row: TableRow) => {
  let selected = false;
  for (var i = 0; i < checkedRows.value.length; i++) {
    if (checkedRows.value[i] === row) {
      selected = true;
      break;
    }
  }
  return selected;
};
const removeChecked = (arr: TableRow[], cb: (row: TableRow) => boolean) => {
  const newArr: TableRow[] = [];

  arr.forEach((row) => {
    if (!cb(row)) {
      newArr.push(row);
    }
  });
  return newArr;
};

const checked = (isChecked: boolean, checkedRow: TableRow | "all") => {
  if (checkedRow === "all") {
    checkedRows.value = toggleSelectAll(isChecked);
  } else {
    if (isChecked) {
      checkedRows.value.push(checkedRow);
    } else {
      checkedRows.value = removeChecked(checkedRows.value, (row) => row === checkedRow);
    }
  }
  emit("checkRows", checkedRows.value);
};

//Toggle checkbox select all when props isShowRowCheckbox is true
function toggleSelectAll(check: boolean) {
  const newArr: TableRow[] = [];
  if (check) {
    tableRowsToDisplay.value.forEach(function (row) {
      newArr.push(row);
    });
  }
  const checkboxes = document.querySelectorAll('input[type^="checkbox"]');
  for (const checkbox of Array.from(checkboxes)) {
    (checkbox as HTMLInputElement).checked = check;
  }
  return newArr;
}

watch(() => props.tableRows, () => {
  doSearch()
}, { deep: true });
</script>

<template>
  <div class="flex items-center justify-between">
    <FormFieldHorizontal class="print:hidden ml-2" v-if="searchableFields">
      <FormControl class="w-64" v-model="searchedText" :icon="mdiFilterMultipleOutline" @change="doSearch"
        placeholder="Search keyword.. " />
    </FormFieldHorizontal>
    <slot name="table-action" />
  </div>

  <div class="overflow-x-auto">
    <table :id="tableName" :class="tableClass">
      <thead>
        <tr>
          <TableCheckboxCell v-if="isShowRowCheckbox" :is-checked="tableRowsToDisplay.length > 0 && tableRowsToDisplay.every((row) => checkedRows.includes(row))"
            @checked="checked($event, 'all')" />
          <th v-for="(field, colindex) in tableHeader" :key="colindex" :style="getColumnHeadStyle(field)">
            <sort-link :name="field.fieldName" :class="getColumnHeadClass(field)">{{ field.label }}</sort-link>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading" v-for="i in (props.rowsPerPage ?? 5)" :key="`skeleton-${i}`" class="animate-pulse">
          <td v-if="isShowRowCheckbox">
            <div class="h-4 w-4 bg-gray-200 rounded"></div>
          </td>
          <td v-for="(field, index) in tableHeader" :key="index" class="py-4 px-6">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </td>
        </tr>
        <tr v-else-if="tableRowsToDisplay.length === 0">
          <td :colspan="tableHeader.length + (isShowRowCheckbox ? 1 : 0)" class="text-center">No records found</td>
        </tr>
        <tr v-else v-for="row in rowsInPage" :key="row.id" :class="isRowFocusSelect ? 'cursor-pointer' : ''"
          @click="isRowFocusSelect ? rowClick(row[rowFocusSelectIdentifier]) : ''">
          <TableCheckboxCell v-if="isShowRowCheckbox" :is-checked="checkIfSelected(row)"
            @checked="checked($event, row)" />

          <td v-for="(field, colindex) in tableHeader" :key="colindex" class="altoff" :data-label="field.label"
            :style="getColumnRowStyle(field)" :class="getColumnRowClass(field)">
            <!-- display value as using vue html -->
            <span v-if="field.hasOwnProperty('type') && field.type === 'html'"
              v-html="getRowFieldValue(row, field.fieldName, field.appendText)" @click.prevent="htmlClick" />
            <!-- display value as date, expecting the column attributes type='date' defined from the parent tableHeader props-->
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'date'">
              {{ core.formatDateForDisplay(getRowFieldValue(row, field.fieldName)) }}
            </span>
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'datetime'">
              {{ core.formatDateToMonthDayYear(getRowFieldValue(row, field.fieldName)) }}
            </span>
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'activeinactive'">
              {{ core.getActiveInactiveText(getRowFieldValue(row, field.fieldName)) }}
            </span>
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'option'">
              {{ field.options![getRowFieldValue(row, field.fieldName)] }}
            </span>
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'status'">
              {{ field.hasOwnProperty("enumerable")
                ? core.getStatusText(getRowFieldValue(row, field.fieldName), field.enumerable!)
                : getRowFieldValue(row, field.fieldName)
              }}
            </span>
            <!-- display value as hyperlink, expecting the column attributes type='hyperlink' defined from the parent tableHeader props-->
            <span v-else-if="field.hasOwnProperty('type') && field.type === 'link'" title="Click to view"
              class="get-started text-blue-500 cursor-pointer hover:text-green-500" @click="openLink(row)"
              v-html="getRowFieldValue(row, field.fieldName) ? getRowFieldValue(row, field.fieldName, field.appendText) : '_ _'" />
            <div v-else-if="field.hasOwnProperty('type') && field.type === 'slot'">
              <slot :name="field.slotName || 'row-action'" :slotProp="row" />
            </div>
            <!-- just display the value -->
            <span v-else :class="getRowFieldValue(row, field.fieldName) ? getColumnRowValueClass(field) : ''">
              {{ getRowFieldValue(row, field.fieldName, field.appendText) }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="table-footer" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
    <BaseLevel>
      <BaseButtons v-if="rowsOnOpen < tableRowsToDisplay.length">
        <small v-if="showTotal">Total : {{ tableRowsToDisplay.length }}</small>
        <div v-if="showWhenVisible==false">
          <BaseButton v-if="tableRowsToDisplay.length > perPage" label="Show All" color="lightDark" small
            @click="showAllRecords" />
        </div>

        <CorePaginate v-if="isPaginated === true && !rowsToDisplayOnOpen" :click-handler="paginateClick"
          :total-records="tableRowsToDisplay.length" :page-size="perPage > 0 ? perPage : 15"
          :container-class="'className'" :pageRange="5" />
        <small>Page Size</small>
        <FormControl v-model="pageSize" type="number" :min="1" :input-class="'rows-per-page'"
          @change="perPage = pageSize" />
      </BaseButtons>
      <small v-else v-if="showTotal">Total : {{ tableRowsToDisplay.length }}</small>
      <div>
        <small v-if="isPaginated === true && !rowsToDisplayOnOpen && showPageInfo" class="mr-2">
          Page {{ currentPageText }} of {{ numberOfPages > 0 ? numberOfPages : 1 }}
        </small>
        <BaseButton v-if="tableRowsToDisplay.length > 0 && showDownloadCsv" label="Download CSV" small color="lightDark"
          @click.prevent="downloadCSVData" />
      </div>
    </BaseLevel>
  </div>
</template>
<style>
.rows-per-page {
  height: 25px !important;
  width: 60px;
  padding: 5px;
  font-size: 80%;
}
</style>
