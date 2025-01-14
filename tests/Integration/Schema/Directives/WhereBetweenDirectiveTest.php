<?php declare(strict_types=1);

namespace Schema\Directives;

use Tests\DBTestCase;
use Tests\Utils\Models\User;

final class WhereBetweenDirectiveTest extends DBTestCase
{
    public function testExplicitNull(): void
    {
        $users = factory(User::class, 2)->create();

        $this->schema = /** @lang GraphQL */'
        scalar DateTime @scalar(class: "Nuwave\\\Lighthouse\\\Schema\\\Types\\\Scalars\\\DateTime")

        type User {
            id: ID!
            created_at: DateTime!
        }

        type Query {
            users(createdBetween: DateTimeRange @whereBetween(key: "created_at")): [User!]! @all
        }

        input DateTimeRange {
            from: DateTime!
            to: DateTime!
        }
        ';

        $this
            ->graphQL(/** @lang GraphQL */'
            {
                users(createdBetween: null) {
                    id
                }
            }
            ')
            ->assertGraphQLErrorFree()
            ->assertJsonCount($users->count(), 'data.users');
    }
}
