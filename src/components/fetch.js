import { ref, onMounted, onUnmounted } from 'vue'
import { fake } from './fake'
import axios from "axios";

export function data(endpoint) {
    let interval = null;
    const data = ref({})

    async function fetchData() {
        console.log(endpoint)
        if (import.meta.env.DEV) {
            data.value = fake(endpoint)
            return
        }

        const response = await axios.get('?data='+endpoint)
        data.value = response.data
    }

    onMounted(() => {
        fetchData()
        interval = setInterval(fetchData,3000)
    })

    onUnmounted(() => clearInterval(interval))

    return data
}