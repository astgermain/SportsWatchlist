<template>
  <div class="px-2 sm:px-0 py-6">
    <h2 class="text-xl font-semibold">My Watchlist</h2>
    <div v-if="loading" class="flex justify-center py-6">
      <loading></loading>
    </div>
    <template v-else>
      <div v-if="watchlist.length" class="mt-6">
        <div v-for="(game, index) in watchlist">
          <NBAGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'nba'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
          <MLBGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'mlb'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
          <NFLGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'nfl'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
          <NHLGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'nhl'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
          <NCAABGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'ncaab'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
          <NCAAFGameListItem
            @game-removed="removeGame"
            v-if="game.game_type === 'ncaaf'"
            :watchlist="true"
            :initialGameData="game"
            class="mt-6 first:mt-0"
            :key="`wg-${game.game_id}`"
          />
        </div>
      </div>
      <div v-else class="mt-6">
        <p>Your watchlist is currently empty!</p>
      </div>
    </template>
  </div>
</template>

<script>
import Loading from "./../components/Loading";
import NBAGameListItem from "./../components/NBAGameListItem";
import MLBGameListItem from "./../components/MLBGameListItem";
import NFLGameListItem from "./../components/NFLGameListItem";
import NHLGameListItem from "./../components/NHLGameListItem";
import NCAAFGameListItem from "./../components/NCAAFGameListItem";
import NCAABGameListItem from "./../components/NCAABGameListItem";

export default {
  components: {
    Loading,
    NBAGameListItem,
    NHLGameListItem,
    NFLGameListItem,
    MLBGameListItem,
    NCAABGameListItem,
    NCAAFGameListItem,
  },
  data() {
    return {
      loading: false,
      watchlist: [],
    };
  },
  created() {
    this.getGames();
  },
  methods: {
    removeGame(gameId) {
      const index = this.watchlist
        .map(game => game.game_id.toString())
        .indexOf(gameId);

      this.watchlist.splice(index, 1);
    },
    getGames() {
      this.loading = true;
      this.$http
        .get(`/api/games?ids=${JSON.stringify(this.$store.state.watchlist)}`)
        .then(response => (this.watchlist = response.data))
        .catch(err => console.log(err))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
