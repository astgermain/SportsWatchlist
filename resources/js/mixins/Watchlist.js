const WatchlistMixin = {
  computed: {
    inWatchlist() {
      return this.$store.state.watchlist.includes(this.game.game_id);
    },
  },
  methods: {
    addToWatchlist() {
      const promise = this.loggedIn ? this.addToDB() : this.addToLocal();

      promise
        .then(response => {
          this.$store.dispatch("fetchWatchlist");
          flash({
            body: "Added to watchlist successfully!",
            type: "success",
          });
          this.added = true;
        })
        .catch(err => console.log(err));
    },
    addToDB() {
      return this.$http.post("/api/watchlist", {
        gameId: this.game.game_id,
      });
    },
    addToLocal() {
      return this.$store.dispatch("addToWatchlist", this.game.game_id);
    },
    removeFromWatchlist() {
      const promise = this.loggedIn
        ? this.removeFromDb()
        : this.removeFromLocal();

      promise
        .then(response => {
          this.$store.dispatch("fetchWatchlist");
          flash({
            body: "Removed from watchlist successfully!",
            type: "success",
          });
          this.added = false;
          this.$emit("game-removed", response);
        })
        .catch(err => console.log(err));
    },
    removeFromDb() {
      return this.$http.delete("/api/watchlist/" + this.game.id);
    },
    removeFromLocal() {
      return this.$store.dispatch("removeFromWatchlist", this.game.game_id);
    },
  },
};

export default WatchlistMixin;