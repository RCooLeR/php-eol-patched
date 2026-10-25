#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "KeccakHash.h"
int main(int argc, char **argv) {
    Keccak_HashInstance ctx;
    unsigned char digest[32];
    if (argc == 2 && !strcmp(argv[1], "large")) {
        const size_t length = (size_t)4294967295ULL;
        unsigned char *data = calloc(length, 1);
        if (!data) return 2;
        if (Keccak_HashInitialize_SHA3_224(&ctx) != SUCCESS ||
            Keccak_HashUpdate(&ctx, data, 8) != SUCCESS ||
            Keccak_HashUpdate(&ctx, data, length * 8) != SUCCESS ||
            Keccak_HashFinal(&ctx, digest) != SUCCESS) return 3;
        for (unsigned int i = 0; i < 28; i++) printf("%02x", digest[i]);
        puts("");
        free(data);
    } else {
        if (Keccak_HashInitialize_SHA3_256(&ctx) != SUCCESS ||
            Keccak_HashUpdate(&ctx, (const unsigned char *)"abc", 24) != SUCCESS ||
            Keccak_HashFinal(&ctx, digest) != SUCCESS) return 4;
        for (unsigned int i = 0; i < 32; i++) printf("%02x", digest[i]);
        puts("");
    }
    return 0;
}
