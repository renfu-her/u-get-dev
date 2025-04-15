<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = '會員管理';
    protected static ?string $navigationLabel = '會員';
    protected static ?string $pluralNavigationLabel = '會員';
    protected static ?string $pluralModelLabel = '會員';
    protected static ?string $modelLabel = '會員';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasPermissionTo('view members');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasPermissionTo('create members');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->hasPermissionTo('edit members');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasPermissionTo('delete members');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('電子郵件')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn(string $operation): bool => $operation === 'edit')
                            ->dehydrated(true),

                        Forms\Components\TextInput::make('phone')
                            ->label('電話')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('password')
                            ->label('密碼')
                            ->password()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->helperText(
                                fn(string $operation): string =>
                                $operation === 'create'
                                    ? '請輸入密碼'
                                    : '留空表示不修改密碼'
                            ),

                        Forms\Components\Toggle::make('is_active')
                            ->label('啟用')
                            ->default(true),

                        Forms\Components\Select::make('gender')
                            ->label('性別')
                            ->options([
                                'male' => '男',
                                'female' => '女',
                                'other' => '其他',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('birthday')
                            ->label('生日')
                            ->format('Y-m-d'),

                        Forms\Components\Textarea::make('address')
                            ->label('地址')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('電子郵件')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('電話')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('性別')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'male' => '男',
                        'female' => '女',
                        'other' => '其他',
                    })
                    ->colors([
                        'primary' => 'male',
                        'danger' => 'female',
                        'warning' => 'other',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('啟用')
                    ->boolean(),

                Tables\Columns\TextColumn::make('birthday')
                    ->label('生日')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('性別')
                    ->options([
                        'male' => '男',
                        'female' => '女',
                        'other' => '其他',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('啟用狀態'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
