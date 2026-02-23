<script setup>
import { formatDate } from "../../../helper.js";
import { ref } from "vue";
import service from "@/Components/Toast/service";
import axios from "axios";

import moment from 'moment';
let toast = service();
const props = defineProps({
    item: Object,
    index: Number,
});

</script>
<template>
    <tr>
         <td>{{ item.sitename }}</td>
        <td>{{moment(item.taskdate).format('MMMM D, YYYY')}}</td>
        <td>{{ item.startdate }}</td>
        <td>{{ item.enddate }}</td>
        <td>{{ item.tasktype.listtask }}</td>
        <td class="text-center"><span :class="[ 'badge', item.status === 'HIT' ? 'bg-success' : 'bg-danger' ]">
    {{ item.status }}</span></td>
        <td >{{ item.remarks}}</td>

    </tr>
</template>

<style scoped>
.custom-link {
  color: black; /* or any other color you prefer */
  text-decoration: none; /* Optional: Remove underline */
}
</style>
