import { computed, ref } from 'vue';

export const useCart = () => {
    const cart = ref<Record<number, any>>({});

    const add = (plan: any) => {
        if (!cart.value[plan.id]) {
            cart.value[plan.id] = { ...plan, quantity: 1 };
        } else {
            cart.value[plan.id].quantity++;
        }
    };

    const remove = (plan: any) => {
        if (cart.value[plan.id]?.quantity > 1) {
            cart.value[plan.id].quantity--;
        } else {
            delete cart.value[plan.id];
        }
    };

    const clear = () => {
        cart.value = {};
    };

    const totalItems = computed(() =>
        Object.values(cart.value).reduce((acc, item) => acc + item.quantity, 0)
    );

    const totalAmount = computed(() =>
        Object.values(cart.value).reduce((acc, item) => acc + item.price * item.quantity, 0).toFixed(2)
    );

    return { cart, add, remove, clear, totalItems, totalAmount };
};
