<script setup lang="ts">

</script>

<template>
    <div id="main" class="bg-indigo-lighten-5">
        <div class="fof">
            <h1>
                <v-empty-state  :headline="$t('static.not_found')" title="404" :text="$t('static.not_found_info')"
                    image="/u/img/logo-blue.png"></v-empty-state>
                    <v-btn color="success" to="/" prepend-icon="mdi-home">{{ $t('static.home_page') }}</v-btn>
            </h1>
        </div>
    </div>
</template>

<style scoped>
* {
    transition: all 0.6s;
}

html {
    height: 100%;
}

body {
    font-family: 'Lato', sans-serif;
    color: #888;
    margin: 0;
}

#main {
    display: table;
    width: 100%;
    height: 100vh;
    text-align: center;
}

.fof {
    display: table-cell;
    vertical-align: middle;
}

.fof h1 {
    font-size: 50px;
    display: inline-block;
    padding-right: 12px;
    animation: type .5s alternate infinite;
}

@keyframes type {
    from {
        box-shadow: inset -3px 0px 0px #888;
    }

    to {
        box-shadow: inset -3px 0px 0px transparent;
    }
}
</style>