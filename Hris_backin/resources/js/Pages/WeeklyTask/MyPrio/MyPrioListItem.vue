<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const props = defineProps({
    item: Object,
    index: Number,
 
});

const emit = defineEmits(["userDeleted", "editUser", "confirmUserDeletion", "showItem", "toggleSelection"]);

const toggleSelection = () => {
    emit("toggleSelection", props.item);
};

const userRole = computed(() => {
    const u = page.props?.auth?.user || {};
    return String(u.role || u.user_type || "").toUpperCase();
});
</script>
<template>
    <tr>
        <!-- <td><input type="checkbox" :checked="selectAll" @change="toggleSelection" /></td> -->
        <td>{{ index + 1 }}</td>
        <td>{{ item.recommnum }}</td>
        <td>{{ item.user }}</td>
        <td>{{ item.branch }}</td>
        <td>{{ item.department }}</td>
        <td>{{ item.created_by }}</td>
        <td>
            <span class="badge" :class="`badge-${item.status.color}`" >{{
                item.status.name
            }} </span>

            &nbsp;<a v-if="item.status.name === 'APPROVED'" href="#" class="badge" :class="`badge-${item.status.color}`">
                <i class="fas fa-download"></i>
            </a>

        </td>

        <td>{{ item.created_at }}</td>


        <td v-if="userRole === 'ADMIN'">

            <a href="#" @click.prevent="$emit('editUser', item)"
                ><i class="fa fa-edit"></i
            ></a>
            <a href="#" @click.prevent="$emit('showItem', item)"
                ><i class="fa fa-eye"></i
            ></a>
            <a href="#" @click.prevent="$emit('confirmUserDeletion', item.id)"
                ><i class="fa fa-trash text-danger ml-2"></i
            ></a>
        </td>
        <td v-if="userRole === 'USER'">


            <a href="#" @click.prevent="$emit('editUser', item)"
                ><i class="fa fa-eye"></i
            ></a>

        </td>
    </tr>
</template>

<style scoped>
.custom-link {
  color: black; /* or any other color you prefer */
  text-decoration: none; /* Optional: Remove underline */
}
</style>
