<?php declare(strict_types=1);

namespace Schema\Directives;

use Tests\DBTestCase;
use Tests\Utils\Models\User;

final class WhereNotBetweenDirectiveTest extends DBTestCase
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
            users(notCreatedBetween: DateTimeRange @whereNotBetween(key: "created_at")): [User!]! @all
        }

        input DateTimeRange {
            from: DateTime!
            to: DateTime!
        }
        ';

        $this
            ->graphQL(/** @lang GraphQL */'
            query ($range: DateTimeRange) {
                users(notCreatedBetween: $range) {
                    id
                }
            }
            ', [
                'range' => null,
            ])
            ->assertGraphQLErrorFree()
            ->assertJsonCount($users->count(), 'data.users');
    }
}
