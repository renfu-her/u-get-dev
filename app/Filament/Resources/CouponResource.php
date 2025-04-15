<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = '訂單管理';

    protected static ?string $navigationLabel = '優惠券';
    protected static ?string $pluralNavigationLabel = '優惠券';
    protected static ?string $pluralModelLabel = '優惠券';
    protected static ?string $modelLabel = '優惠券';
    protected static ?int $navigationSort = 4;

    // public static function canViewAny(): bool
    // {
    //     return Auth::user()?->can('view coupons') ?? false;
    // }

    // public static function canCreate(): bool
    // {
    //     return Auth::user()?->can('create coupons') ?? false;
    // }

    // public static function canEdit(Model $record): bool
    // {
    //     return Auth::user()?->can('edit coupons') ?? false;
    // }

    // public static function canDelete(Model $record): bool
    // {
    //     return Auth::user()?->can('delete coupons') ?? false;
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('商店'),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('優惠碼'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('名稱'),

                Forms\Components\RichEditor::make('description')
                    ->label('描述')
                    ->columnSpanFull(),

                Forms\Components\Select::make('type')
                    ->options([
                        'percentage' => '百分比',
                        'fixed' => '固定金額',
                    ])
                    ->required()
                    ->default('fixed')
                    ->label('類型'),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->prefix(fn(Forms\Get $get) => $get('type') === 'percentage' ? '%' : 'NT$')
                    ->label('優惠值'),

                Forms\Components\TextInput::make('min_purchase')
                    ->numeric()
                    ->prefix('NT$')
                    ->label('最低消費'),

                Forms\Components\TextInput::make('usage_limit')
                    ->numeric()
                    ->label('使用次數限制'),

                Forms\Components\DateTimePicker::make('start_at')
                    ->required()
                    ->label('開始時間'),

                Forms\Components\DateTimePicker::make('end_at')
                    ->required()
                    ->label('結束時間'),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->label('啟用')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->sortable()
                    ->searchable()
                    ->label('商店'),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('優惠碼'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('名稱'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'percentage' => '百分比',
                        'fixed' => '固定金額',
                    })
                    ->label('類型'),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(function ($record) {
                        return $record->type === 'percentage' ? "{$record->value}%" : "NT$" . $record->value;
                    })
                    ->label('優惠值'),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->numeric()
                    ->label('使用限制'),

                Tables\Columns\TextColumn::make('used_count')
                    ->numeric()
                    ->label('已使用'),

                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->label('開始時間'),

                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->label('結束時間'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('啟用'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store')
                    ->relationship('store', 'name')
                    ->label('商店'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => '百分比',
                        'fixed' => '固定金額',
                    ])
                    ->label('類型'),

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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
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