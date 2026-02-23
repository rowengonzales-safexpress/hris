<script>
import BaseButton from "@/Components/BaseButton.vue";
export default {
  components: {
    BaseButton: BaseButton,
  },
  data() {
    return {
      innerValue: 0,
    };
  },
  props: {
    modelValue: {
      type: Number,
    },
    totalRecords: {
      type: Number,
    },
    pageSize: {
      type: Number,
    },
    initialPage: {
      type: Number,
      default: 0,
    },
    forcePage: {
      type: Number,
    },
    clickHandler: {
      type: Function,
      default: () => {},
    },
    pageRange: {
      type: Number,
      default: 3,
    },
    marginPages: {
      type: Number,
      default: 1,
    },
    firstButtonText: {
      type: String,
      default: "First",
    },
    lastButtonText: {
      type: String,
      default: "Last",
    },
    prevButtonText: {
      type: String,
      default: "Prior",
    },
    nextButtonText: {
      type: String,
      default: "Next",
    },
    breakViewText: {
      type: String,
      default: "…",
    },
    containerClass: {
      type: String,
      default: "pagination",
    },
    pageClass: {
      type: String,
      default: "page-item",
    },
    pageLinkClass: {
      type: String,
      default: "page-link",
    },
    prevClass: {
      type: String,
      default: "page-item",
    },
    prevLinkClass: {
      type: String,
      default: "page-link",
    },
    nextClass: {
      type: String,
      default: "page-item",
    },
    nextLinkClass: {
      type: String,
      default: "page-link",
    },
    firstLastButton: {
      type: Boolean,
      default: false,
    },
    showPageNumbers: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    currentPage: {
      get: function () {
        return this.modelValue || this.innerValue;
      },
      set: function (newValue) {
        this.innerValue = newValue;
      },
    },
    pageCount: function () {
      return Math.ceil(this.totalRecords / this.pageSize);
    },

    pages: function () {
      let items = {};
      if (this.pageCount <= this.pageRange) {
        for (let index = 0; index < this.pageCount; index++) {
          let page = {
            index: index,
            content: index + 1,
            currentPage: index === this.currentPage - 1,
          };
          items[index] = page;
        }
      } else {
        const halfPageRange = Math.floor(this.pageRange / 2);

        let setPageItem = (index) => {
          let page = {
            index: index,
            content: index + 1,
            currentPage: index === this.currentPage - 1,
          };

          items[index] = page;
        };

        let setBreakView = (index) => {
          let breakView = {
            disabled: true,
            breakView: true,
          };

          items[index] = breakView;
        };

        // 1st - loop thru low end of margin pages
        for (let i = 0; i < this.marginPages; i++) {
          setPageItem(i);
        }

        // 2nd - loop thru currentPage range
        let currentPageRangeLow = 0;
        if (this.currentPage - halfPageRange > 0) {
          currentPageRangeLow = this.currentPage - 1 - halfPageRange;
        }

        let currentPageRangeHigh = currentPageRangeLow + this.pageRange - 1;
        if (currentPageRangeHigh >= this.pageCount) {
          currentPageRangeHigh = this.pageCount - 1;
          currentPageRangeLow = currentPageRangeHigh - this.pageRange + 1;
        }

        for (
          let i = currentPageRangeLow;
          i <= currentPageRangeHigh && i <= this.pageCount;
          i++
        ) {
          setPageItem(i);
        }

        // Check if there is breakView in the left of currentPage range
        if (currentPageRangeLow > this.marginPages) {
          setBreakView(currentPageRangeLow - 1);
        }

        // Check if there is breakView in the right of currentPage range
        if (currentPageRangeHigh + 1 < this.pageCount - this.marginPages) {
          setBreakView(currentPageRangeHigh + 1);
        }

        // 3rd - loop thru high end of margin pages
        for (
          let i = this.pageCount - 1;
          i >= this.pageCount - this.marginPages;
          i--
        ) {
          setPageItem(i);
        }
      }
      return items;
    },

    firstPagecurrentPage: function () {
      return this.currentPage === 0;
    },
    lastPagecurrentPage: function () {
      return this.currentPage === this.pageCount - 1 || this.pageCount === 0;
    },
  },
  methods: {
    handlePagecurrentPage(currentPage) {
      if (this.currentPage === currentPage) return;

      this.innerValue = currentPage;
      this.$emit("update:modelValue", currentPage);
      this.clickHandler(currentPage);
    },
    prevPage() {
      if (this.currentPage <= 0) return;

      this.handlePagecurrentPage(this.currentPage - 1);
    },
    nextPage() {
      if (this.currentPage >= this.pageCount) return;

      this.handlePagecurrentPage(this.currentPage + 1);
    },

    selectFirstPage() {
      if (this.currentPage <= 0) return;

      this.handlePagecurrentPage(0);
    },
    selectLastPage() {
      if (this.currentPage >= this.pageCount) return;

      this.handlePagecurrentPage(this.pageCount - 1);
    },
  },
  beforeMount() {
    this.innerValue = this.initialPage;
  },
  beforeUpdate() {
    if (this.forcePage === undefined) return;
    if (this.forcePage !== this.currentPage) {
      this.currentPage = this.forcePage;
    }
  },
  watch : {
    pageSize (value) {
      if (this.pageCount <= this.currentPage && parseInt(value) > 0) {
        this.selectFirstPage()
      }
    }
  }
};
</script>

<template>
  <div >
    <BaseButton
      v-if="(pageCount - 1)  > pageRange && firstLastButton"
      :class="[prevClass]"
      :label="firstButtonText"
      color="whiteDark"
      small
      @click="selectFirstPage()"
      @keyup.enter="selectFirstPage()"
      :disabled="firstPagecurrentPage"
      class="mx-1"
    />
    <BaseButton
      v-if="(pageCount - 1)  > pageRange"
      :class="[prevClass]"
      :label="prevButtonText"
      color="whiteDark"
      small
      @click="prevPage"
      @keyup.enter="prevPage"
      :disabled="firstPagecurrentPage"
      class="mx-1"
    />
    <BaseButton
      v-if="(pageCount - 1)  > pageRange"
      :class="[nextClass]"
      :label="nextButtonText"
      color="whiteDark"
      small
      @click="nextPage()"
      @keyup.enter="nextPage()"
      :disabled="lastPagecurrentPage"
      class="mx-1"
    />
    <BaseButton
      v-if="(pageCount - 1) > pageRange && firstLastButton"
      :class="[nextClass]"
      :label="lastButtonText"
      color="whiteDark"
      small
      @click="selectLastPage()"
      @keyup.enter="selectLastPage()"
      :disabled="lastPagecurrentPage"
      class="mx-1"
    />
    <BaseButton
      v-if="showPageNumbers"
      v-for="page in pages"
      :key="page.index"
      :label="page.breakView ? breakViewText : page.content"
      :color="page.index === currentPage ? 'lightDark' : 'whiteDark'"
      small
      @click="page.breakView ? null : handlePagecurrentPage(page.index)"
      @keyup.enter="page.breakView ? null : handlePagecurrentPage(page.index)"
      class="mx-1"
      :disabled="page.breakView"
    />
  </div>
</template>

<style scoped></style>
